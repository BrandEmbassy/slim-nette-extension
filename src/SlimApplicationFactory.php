<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim;

use BrandEmbassy\Slim\DI\ServiceProvider;
use BrandEmbassy\Slim\Middleware\MiddlewareFactory;
use BrandEmbassy\Slim\Request\Request;
use BrandEmbassy\Slim\Route\OnlyNecessaryRoutesProvider;
use BrandEmbassy\Slim\Route\RouteRegister;
use LogicException;
use Nette\DI\Container;
use Slim\Container as SlimContainer;
use function apcu_enabled;
use function assert;
use function implode;
use function in_array;
use function is_callable;
use function sprintf;

/**
 * @final
 */
class SlimApplicationFactory
{
    public const SLIM_CONFIGURATION = 'slimConfiguration';
    public const SETTINGS = 'settings';
    public const BEFORE_ROUTE_MIDDLEWARES = 'beforeRouteMiddlewares';
    public const HANDLERS = 'handlers';
    public const BEFORE_REQUEST_MIDDLEWARES = 'beforeRequestMiddlewares';
    public const ROUTES = 'routes';
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
     * @var RouteRegister
     */
    private $routeRegister;

    /**
     * @var OnlyNecessaryRoutesProvider
     */
    private $onlyNecessaryRoutesProvider;


    /**
     * @param mixed[] $configuration
     */
    public function __construct(
        array $configuration,
        Container $container,
        MiddlewareFactory $middlewareFactory,
        SlimContainerFactory $slimContainerFactory,
        RouteRegister $routeRegister,
        OnlyNecessaryRoutesProvider $onlyNecessaryRoutesProvider
    ) {
        $this->configuration = $configuration;
        $this->container = $container;
        $this->middlewareFactory = $middlewareFactory;
        $this->slimContainerFactory = $slimContainerFactory;
        $this->routeRegister = $routeRegister;
        $this->onlyNecessaryRoutesProvider = $onlyNecessaryRoutesProvider;
    }


    public function create(): SlimApp
    {
        /** @var array<string, mixed> $slimConfiguration */
        $slimConfiguration = $this->configuration[self::SLIM_CONFIGURATION];
        $detectTyposInRouteConfiguration = (bool)$this->getSlimSettings(
            SlimSettings::DETECT_TYPOS_IN_ROUTE_CONFIGURATION,
            true
        );
        $registerOnlyNecessaryRoutes = (bool)$this->getSlimSettings(
            SlimSettings::REGISTER_ONLY_NECESSARY_ROUTES,
            false
        );
        $useApcuCache = (bool)$this->getSlimSettings(
            SlimSettings::USE_APCU_CACHE,
            false
        );
        if ($useApcuCache && !apcu_enabled()) {
            throw new LogicException('APCU cache is not enabled');
        }

        $slimContainer = $this->slimContainerFactory->create($slimConfiguration);

        $app = new SlimApp($slimContainer);

        $routesToRegister = $this->configuration[self::ROUTES];
        if ($registerOnlyNecessaryRoutes) {
            /** @var Request $request */
            $request = $slimContainer->get('request');
            $requestUri = $request->getServerParam('REQUEST_URI');

            $routesToRegister = $this->onlyNecessaryRoutesProvider->getRoutes(
                $requestUri,
                $routesToRegister,
                $useApcuCache
            );
        }

        foreach ($routesToRegister as $apiNamespace => $routes) {
            $this->registerApi($apiNamespace, $routes, $detectTyposInRouteConfiguration);
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
    private function registerApi(string $apiNamespace, array $routes, bool $detectTyposInRouteConfiguration): void
    {
        foreach ($routes as $routePattern => $routeData) {
            $this->routeRegister->register($apiNamespace, $routePattern, $routeData, $detectTyposInRouteConfiguration);
        }
    }


    /**
     * @param mixed $defaultValue
     *
     * @return mixed
     */
    private function getSlimSettings(string $key, $defaultValue)
    {
        return $this->configuration[self::SLIM_CONFIGURATION][self::SETTINGS][$key] ?? $defaultValue;
    }
}
