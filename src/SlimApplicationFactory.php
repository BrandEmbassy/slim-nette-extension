<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim;

use ArrayAccess;
use BrandEmbassy\Slim\DI\ServiceProvider;
use BrandEmbassy\Slim\Middleware\MiddlewareFactory;
use BrandEmbassy\Slim\Request\Request;
use BrandEmbassy\Slim\Route\OnlyNecessaryRoutesProvider;
use BrandEmbassy\Slim\Route\RouteRegister;
use LogicException;
use Nette\DI\Container;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Factory\AppFactory;
use Slim\Interfaces\RouteCollectorProxyInterface;
use Slim\Psr7\Factory\ResponseFactory;
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

    public const AFTER_ROUTE_MIDDLEWARES = 'afterRouteMiddlewares';

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
    private array $configuration;

    private Container $container;

    private MiddlewareFactory $middlewareFactory;

    private SlimContainerFactory $slimContainerFactory;

    private RouteRegister $routeRegister;

    private OnlyNecessaryRoutesProvider $onlyNecessaryRoutesProvider;


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
            true,
        );
        $registerOnlyNecessaryRoutes = (bool)$this->getSlimSettings(
            SlimSettings::REGISTER_ONLY_NECESSARY_ROUTES,
            false,
        );
        $useApcuCache = (bool)$this->getSlimSettings(
            SlimSettings::USE_APCU_CACHE,
            true,
        );
        $disableUsingSlimContainer = (bool)$this->getSlimSettings(
            SlimSettings::DISABLE_USING_SLIM_CONTAINER,
            false,
        );

        $routeApiNamesAlwaysInclude = (array)$this->getSlimSettings(
            SlimSettings::ROUTE_API_NAMES_ALWAYS_INCLUDE,
            [],
        );

        if ($useApcuCache && !apcu_enabled()) {
            // @intentionally For cli scripts is APCU disabled by default
            $useApcuCache = false;
        }

        if ($disableUsingSlimContainer && !($this->container instanceof ContainerInterface)) {
            throw new LogicException('Container must be instance of \Psr\Container\ContainerInterface');
        }

        // Create a compatibility container for backward compatibility
        $compatContainer = new CompatibilityContainer($this->container);
        
        // Store settings in the compatibility container
        if (isset($slimConfiguration[self::SETTINGS])) {
            $compatContainer['settings'] = $slimConfiguration[self::SETTINGS];
        }

        // Create response factory
        $responseFactory = new ResponseFactory();

        // Create custom SlimApp instance with the compatibility container
        $slimApp = new SlimApp($responseFactory, $compatContainer);
        
        // Set the app reference in the container so it can provide the router
        $compatContainer->setApp($slimApp);

        $routesToRegister = $this->configuration[self::ROUTES];
        if ($registerOnlyNecessaryRoutes) {
            $configData = $this->slimContainerFactory->create($slimConfiguration);
            /** @var Request $request */
            $request = $configData['request'];
            $requestUri = $request->getServerParam('REQUEST_URI');

            $routesToRegister = $this->onlyNecessaryRoutesProvider->getRoutes(
                $requestUri,
                $routesToRegister,
                $useApcuCache,
                $routeApiNamesAlwaysInclude,
            );
        }

        foreach ($routesToRegister as $apiNamespace => $routes) {
            $this->registerApi($slimApp, $apiNamespace, $routes, $detectTyposInRouteConfiguration);
        }

        $this->registerHandlers(
            $compatContainer,
            $slimApp,
            $this->configuration[self::HANDLERS],
        );

        foreach ($this->configuration[self::BEFORE_REQUEST_MIDDLEWARES] as $middleware) {
            $middlewareService = $this->middlewareFactory->createFromIdentifier($middleware);
            $slimApp->add($middlewareService);
        }

        return $slimApp;
    }


    /**
     * @param array<string, string> $handlers
     */
    private function registerHandlers(
        CompatibilityContainer $container,
        SlimApp $app,
        array $handlers
    ): void {
        foreach ($handlers as $handlerName => $handlerClass) {
            $this->validateHandlerName($handlerName);
            $handlerService = ServiceProvider::getService($this->container, $handlerClass);
            assert(is_callable($handlerService));

            // Store handlers in the compatibility container
            $container[$handlerName] = $handlerService;
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
            implode(', ', self::ALLOWED_HANDLERS),
        );

        throw new LogicException($error);
    }


    /**
     * @param mixed[] $routes
     */
    private function registerApi(
        RouteCollectorProxyInterface $app,
        string $apiNamespace,
        array $routes,
        bool $detectTyposInRouteConfiguration
    ): void {
        foreach ($routes as $routePattern => $routeData) {
            $this->routeRegister->register($apiNamespace, $routePattern, $routeData, $detectTyposInRouteConfiguration, $app);
        }
    }


    /**
     * @param bool|array<mixed> $defaultValue
     */
    private function getSlimSettings(string $key, bool|array $defaultValue): mixed
    {
        return $this->configuration[self::SLIM_CONFIGURATION][self::SETTINGS][$key] ?? $defaultValue;
    }
}
