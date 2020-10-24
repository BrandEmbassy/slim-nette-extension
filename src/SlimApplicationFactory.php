<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim;

use BrandEmbassy\Slim\Controller\ControllerDefinition;
use BrandEmbassy\Slim\Controller\ControllerDefinitionFactory;
use BrandEmbassy\Slim\DI\ServiceProvider;
use BrandEmbassy\Slim\Middleware\MiddlewareFactory;
use BrandEmbassy\Slim\Route\RouteDefinitionFactory;
use LogicException;
use Nette\DI\Container;
use Slim\Container as SlimContainer;
use function assert;
use function implode;
use function in_array;
use function is_callable;
use function sprintf;
use function trim;

final class SlimApplicationFactory
{
    public const SLIM_CONFIGURATION = 'slimConfiguration';
    public const BEFORE_ROUTE_MIDDLEWARES = 'beforeRouteMiddlewares';
    public const HANDLERS = 'handlers';
    public const BEFORE_REQUEST_MIDDLEWARES = 'beforeRequestMiddlewares';
    public const ROUTES = 'routes';
    public const CONTROLLERS = 'controllers';
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
     * @var array<callable>
     */
    private $beforeRoutesMiddlewares;

    /**
     * @var RouteDefinitionFactory
     */
    private $routeDefinitionFactory;

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
     * @param mixed[] $configuration
     */
    public function __construct(
        array $configuration,
        Container $container,
        RouteDefinitionFactory $routeDefinitionFactory,
        MiddlewareFactory $middlewareFactory,
        SlimContainerFactory $slimContainerFactory,
        ControllerDefinitionFactory $controllerDefinitionFactory
    ) {
        $this->configuration = $configuration;
        $this->container = $container;
        $this->beforeRoutesMiddlewares = [];
        $this->routeDefinitionFactory = $routeDefinitionFactory;
        $this->middlewareFactory = $middlewareFactory;
        $this->slimContainerFactory = $slimContainerFactory;
        $this->controllerDefinitionFactory = $controllerDefinitionFactory;
    }


    public function create(): SlimApp
    {
        $slimContainer = $this->slimContainerFactory->create($this->configuration[self::SLIM_CONFIGURATION]);

        $app = new SlimApp($slimContainer);

        $this->registerBeforeRouteMiddlewares($this->configuration[self::BEFORE_ROUTE_MIDDLEWARES]);

        foreach ($this->configuration[self::ROUTES] as $apiVersion => $routes) {
            $this->registerApi($app, $apiVersion, $routes);
        }

        foreach ($this->configuration[self::CONTROLLERS] as $apiVersion => $controllers) {
            $this->registerControllers($app, $apiVersion, $controllers);
        }

        $this->registerHandlers($slimContainer, $this->configuration[self::HANDLERS]);

        foreach ($this->configuration[self::BEFORE_REQUEST_MIDDLEWARES] as $middleware) {
            $middlewareService = $this->middlewareFactory->createFromConfig($middleware);
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
    private function registerApi(SlimApp $app, string $version, array $routes): void
    {
        foreach ($routes as $routeName => $routeData) {
            $urlPattern = $this->createUrlPattern($version, $routeName);

            $this->registerRoute($app, $routeData, $urlPattern);
        }
    }


    /**
     * @param mixed[] $routeData
     */
    private function registerRoute(SlimApp $app, array $routeData, string $urlPattern): void
    {
        foreach ($routeData as $method => $routeDefinitionData) {
            $routeDefinition = $this->routeDefinitionFactory->create($method, (array)$routeDefinitionData);

            $routeToAdd = $app->map([$routeDefinition->getMethod()], $urlPattern, $routeDefinition->getRoute());

            foreach ($routeDefinition->getMiddlewares() as $middleware) {
                $routeToAdd->add($middleware);
            }

            foreach ($this->beforeRoutesMiddlewares as $middleware) {
                $routeToAdd->add($middleware);
            }
        }
    }


    /**
     * @param string[][] $controllers
     */
    private function registerControllers(SlimApp $app, string $version, array $controllers): void
    {
        foreach ($controllers as $controllerName => $controllerData) {
            $urlPattern = $this->createUrlPattern($version, $controllerName);
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

        foreach ($controllerDefinition->getMethods() as $action => $method) {
            $callable = sprintf('%s:%s', $controllerDefinition->getControllerIdentifier(), $action);
            $middlewareMethod = sprintf('%s:middleware', $controllerDefinition->getControllerIdentifier());

            $app->map([$method], $urlPattern, $callable)->add($middlewareMethod);
        }
    }


    private function createUrlPattern(string $version, string $routeName): string
    {
        $version = trim($version, '/');
        $routeName = trim($routeName, '/');

        if ($version !== '') {
            $version = '/' . $version;
        }

        if ($routeName !== '') {
            $routeName = '/' . $routeName;
        }

        return $version . $routeName;
    }


    /**
     * @param string[] $beforeRouteMiddlewares
     */
    private function registerBeforeRouteMiddlewares(array $beforeRouteMiddlewares): void
    {
        foreach ($beforeRouteMiddlewares as $globalMiddleware) {
            $middlewareService = $this->middlewareFactory->createFromConfig($globalMiddleware);

            $this->beforeRoutesMiddlewares[] = $middlewareService;
        }
    }
}
