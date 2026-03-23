<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim;

use BrandEmbassy\Slim\DI\ServiceProvider;
use BrandEmbassy\Slim\Middleware\MiddlewareFactory;
use BrandEmbassy\Slim\Route\OnlyNecessaryRoutesProvider;
use BrandEmbassy\Slim\Route\RouteRegister;
use LogicException;
use Nette\DI\Container;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Interfaces\RouteCollectorProxyInterface;
use Slim\Psr7\Factory\ResponseFactory;
use function apcu_enabled;
use function assert;
use function function_exists;
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

    private RouteRegister $routeRegister;

    private OnlyNecessaryRoutesProvider $onlyNecessaryRoutesProvider;


    /**
     * @param mixed[] $configuration
     */
    public function __construct(
        array $configuration,
        Container $container,
        MiddlewareFactory $middlewareFactory,
        RouteRegister $routeRegister,
        OnlyNecessaryRoutesProvider $onlyNecessaryRoutesProvider
    ) {
        $this->configuration = $configuration;
        $this->container = $container;
        $this->middlewareFactory = $middlewareFactory;
        $this->routeRegister = $routeRegister;
        $this->onlyNecessaryRoutesProvider = $onlyNecessaryRoutesProvider;
    }


    public function create(): SlimApp
    {
        $slimContainer = new SlimContainer($this->container);
        $slimApp = new SlimApp(new ResponseFactory(), $slimContainer);

        $this->registerRoutes($slimApp);
        $this->registerMiddlewares($slimApp);

        return $slimApp;
    }


    private function registerRoutes(SlimApp $slimApp): void
    {
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
        $routeApiNamesAlwaysInclude = (array)$this->getSlimSettings(
            SlimSettings::ROUTE_API_NAMES_ALWAYS_INCLUDE,
            [],
        );

        if ($useApcuCache && (!function_exists('apcu_enabled') || !apcu_enabled())) {
            // @intentionally For cli scripts is APCU disabled by default or extension not installed
            $useApcuCache = false;
        }

        $routesToRegister = $this->configuration[self::ROUTES];
        if ($registerOnlyNecessaryRoutes) {
            $requestUri = $_SERVER['REQUEST_URI'] ?? null;

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
    }


    /**
     * Registers all middleware in the correct order for Slim 4's LIFO stack.
     * Last added = first to execute on request (outermost).
     *
     * Execution order on request:
     *   1. beforeRequestMiddlewares (CORS, trace ID, …)
     *   2. Error handling
     *   3. Routing (resolves matched route)
     *   4. Body parsing (JSON, XML, form-urlencoded)
     *   5. Route-level middlewares + handler
     */
    private function registerMiddlewares(SlimApp $slimApp): void
    {
        $slimApp->addBodyParsingMiddleware();
        $slimApp->addRoutingMiddleware();

        $handlers = $this->resolveHandlers();
        $errorMiddleware = $slimApp->addErrorMiddleware(true, true, true);

        if (isset($handlers['errorHandler'])) {
            $errorMiddleware->setDefaultErrorHandler($handlers['errorHandler']);
        }

        if (isset($handlers['notFoundHandler'])) {
            $errorMiddleware->setErrorHandler(HttpNotFoundException::class, $handlers['notFoundHandler']);
        }

        if (isset($handlers['notAllowedHandler'])) {
            $errorMiddleware->setErrorHandler(HttpMethodNotAllowedException::class, $handlers['notAllowedHandler']);
        }

        foreach ($this->configuration[self::BEFORE_REQUEST_MIDDLEWARES] as $middlewareIdentifier) {
            $slimApp->add($this->middlewareFactory->createFromIdentifier($middlewareIdentifier));
        }
    }


    /**
     * @return array<string, callable>
     */
    private function resolveHandlers(): array
    {
        $resolved = [];

        foreach ($this->configuration[self::HANDLERS] as $handlerName => $handlerClass) {
            $this->validateHandlerName($handlerName);
            $handlerService = ServiceProvider::getService($this->container, $handlerClass);
            assert(is_callable($handlerService));
            $resolved[$handlerName] = $handlerService;
        }

        return $resolved;
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
