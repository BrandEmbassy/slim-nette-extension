<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim;

use BrandEmbassy\Slim\Controller\ControllerDefinition;
use BrandEmbassy\Slim\Controller\ControllerDefinitionFactory;
use BrandEmbassy\Slim\DI\ServiceProvider;
use BrandEmbassy\Slim\Middleware\MiddlewareFactory;
use BrandEmbassy\Slim\Route\RouteDefinitionFactory;
use BrandEmbassy\Slim\Route\RouteRegister;
use BrandEmbassy\Slim\Route\UrlPatternResolver;
use LogicException;
use Nette\DI\Container;
use Slim\Container as SlimContainer;
use function assert;
use function implode;
use function in_array;
use function is_callable;
use function sprintf;

final class SlimApplicationFactory
{
    public const SLIM_CONFIGURATION = 'slimConfiguration';
    public const BEFORE_ROUTE_MIDDLEWARES = 'beforeRouteMiddlewares';
    public const HANDLERS = 'handlers';
    public const BEFORE_REQUEST_MIDDLEWARES = 'beforeRequestMiddlewares';
    public const ROUTES = 'routes';
    public const CONTROLLERS = 'controllers';
    public const API_PREFIX = 'apiPrefix';
    public const MIDDLEWARE_GROUPS = 'middlewareGroups';
    private const ALLOWED_HANDLERS = [
        'notFoundHandler',
        'notAllowedHandler',
        'errorHandler',
        'phpErrorHandler',
    ];

    /**
     * @var mixed[]
     */
    private $configuration;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var MiddlewareFactory
     */
    private $middlewareFactory;

    /**
     * @var SlimContainerFactory
     */
    private $slimContainerFactory;

    /**
     * @var ControllerDefinitionFactory
     */
    private $controllerDefinitionFactory;

    /**
     * @var UrlPatternResolver
     */
    private $urlPatternResolver;

    /**
     * @var RouteRegister
     */
    private $routeRegister;


    /**
     * @param mixed[] $configuration
     */
    public function __construct(
        array $configuration,
        Container $container,
        MiddlewareFactory $middlewareFactory,
        SlimContainerFactory $slimContainerFactory,
        ControllerDefinitionFactory $controllerDefinitionFactory,
        UrlPatternResolver $urlPatternResolver,
        RouteRegister $routeRegister
    ) {
        $this->configuration = $configuration;
        $this->container = $container;
        $this->middlewareFactory = $middlewareFactory;
        $this->slimContainerFactory = $slimContainerFactory;
        $this->controllerDefinitionFactory = $controllerDefinitionFactory;
        $this->urlPatternResolver = $urlPatternResolver;
        $this->routeRegister = $routeRegister;
    }


    public function create(): SlimApp
    {
        $slimContainer = $this->slimContainerFactory->create($this->configuration[self::SLIM_CONFIGURATION]);

        $app = new SlimApp($slimContainer);

        foreach ($this->configuration[self::ROUTES] as $apiVersion => $routes) {
            $this->registerApi($apiVersion, $routes);
        }

        foreach ($this->configuration[self::CONTROLLERS] as $apiVersion => $controllers) {
            $this->registerControllers($app, $apiVersion, $controllers);
        }

        $this->registerHandlers($slimContainer, $this->configuration[self::HANDLERS]);

        foreach ($this->configuration[self::BEFORE_REQUEST_MIDDLEWARES] as $middleware) {
            $middlewareService = $this->middlewareFactory->createFromIdentifier($middleware);
            $app->add($middlewareService);
        }

        return $app;
    }


    /**
     * @param array<string, string> $handlers
     */
    private function registerHandlers(SlimContainer $slimContainer, array $handlers): void
    {
        foreach ($handlers as $handlerName => $handlerClass) {
            $this->validateHandlerName($handlerName);
            $handlerService = ServiceProvider::getService($this->container, $handlerClass);
            assert(is_callable($handlerService));

            $slimContainer[$handlerName] = static function () use ($handlerService) {
                return $handlerService;
            };
        }
    }


    private function validateHandlerName(string $handlerName): void
    {
        if (in_array($handlerName, self::ALLOWED_HANDLERS, true)) {
            return;
        }

        $error = sprintf(
            '%s handler name is not allowed, available handlers: %s',
            $handlerName,
            implode(', ', self::ALLOWED_HANDLERS)
        );

        throw new LogicException($error);
    }


    /**
     * @param mixed[] $routes
     */
    private function registerApi(string $version, array $routes): void
    {
        foreach ($routes as $routeName => $routeData) {
            $this->routeRegister->register($version, $routeName, $routeData);
        }
    }


    /**
     * @param string[][] $controllers
     */
    private function registerControllers(SlimApp $app, string $version, array $controllers): void
    {
        foreach ($controllers as $controllerName => $controllerData) {
            $urlPattern = $this->urlPatternResolver->resolve($version, $controllerName);
            $controllerDefinition = $this->controllerDefinitionFactory->create($controllerData);

            $this->registerController($app, $controllerDefinition, $urlPattern);
        }
    }


    private function registerController(
        SlimApp $app,
        ControllerDefinition $controllerDefinition,
        string $urlPattern
    ): void {
        $app->getContainer()[$controllerDefinition->getControllerIdentifier()] = $controllerDefinition->getController();

        foreach ($controllerDefinition->getMethods() as $method => $action) {
            $callable = sprintf('%s:%s', $controllerDefinition->getControllerIdentifier(), $action);
            $middlewareMethod = sprintf('%s:middleware', $controllerDefinition->getControllerIdentifier());

            $app->map([$method], $urlPattern, $callable)->add($middlewareMethod);
        }
    }
}
