<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim;

use BrandEmbassy\Slim\DI\NettePsrContainerAdapter;
use BrandEmbassy\Slim\DI\ServiceProvider;
use BrandEmbassy\Slim\Middleware\MiddlewareFactory;
use BrandEmbassy\Slim\Request\RequestFactory;
use BrandEmbassy\Slim\Response\ResponseFactory;
use BrandEmbassy\Slim\Route\OnlyNecessaryRoutesProvider;
use BrandEmbassy\Slim\Route\RouteRegister;
use LogicException;
use Nette\DI\Container;
use Slim\CallableResolver;
use Slim\Collection;
use Slim\Handlers\PhpError;
use Slim\Handlers\Strategies\RequestResponse;
use Slim\Http\Environment;
use Slim\Interfaces\RouterInterface;
use function apcu_enabled;
use function array_merge;
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

    private const DEFAULT_SETTINGS = [
        'httpVersion' => '1.1',
        'responseChunkSize' => 4096,
        'outputBuffering' => 'append',
        'determineRouteBeforeAppMiddleware' => false,
        'displayErrorDetails' => false,
        'addContentLengthHeader' => true,
        'routerCacheFile' => false,
    ];

    /**
     * @var mixed[]
     */
    private array $configuration;

    private Container $container;

    private MiddlewareFactory $middlewareFactory;

    private RequestFactory $requestFactory;

    private ResponseFactory $responseFactory;

    private RouterInterface $router;

    private RouteRegister $routeRegister;

    private OnlyNecessaryRoutesProvider $onlyNecessaryRoutesProvider;


    /**
     * @param mixed[] $configuration
     */
    public function __construct(
        array $configuration,
        Container $container,
        MiddlewareFactory $middlewareFactory,
        RequestFactory $requestFactory,
        ResponseFactory $responseFactory,
        RouterInterface $router,
        RouteRegister $routeRegister,
        OnlyNecessaryRoutesProvider $onlyNecessaryRoutesProvider
    ) {
        $this->configuration = $configuration;
        $this->container = $container;
        $this->middlewareFactory = $middlewareFactory;
        $this->requestFactory = $requestFactory;
        $this->responseFactory = $responseFactory;
        $this->router = $router;
        $this->routeRegister = $routeRegister;
        $this->onlyNecessaryRoutesProvider = $onlyNecessaryRoutesProvider;
    }


    public function create(): SlimApp
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

        if ($useApcuCache && !apcu_enabled()) {
            // @intentionally For cli scripts is APCU disabled by default
            $useApcuCache = false;
        }

        $psrContainer = new NettePsrContainerAdapter($this->container);

        $this->registerSlimServicesInContainer($this->container, $psrContainer);

        $app = new SlimApp($psrContainer);

        $routesToRegister = $this->configuration[self::ROUTES];
        if ($registerOnlyNecessaryRoutes) {
            $request = $this->requestFactory->create();
            $requestUri = $request->getServerParams()['REQUEST_URI'] ?? '';

            $routesToRegister = $this->onlyNecessaryRoutesProvider->getRoutes(
                $requestUri,
                $routesToRegister,
                $useApcuCache,
                $routeApiNamesAlwaysInclude,
            );
        }

        foreach ($routesToRegister as $apiNamespace => $routes) {
            $this->registerApi($apiNamespace, $routes, $detectTyposInRouteConfiguration);
        }

        $this->registerHandlers($this->container, $this->configuration[self::HANDLERS]);

        foreach ($this->configuration[self::BEFORE_REQUEST_MIDDLEWARES] as $middleware) {
            $middlewareService = $this->middlewareFactory->createFromIdentifier($middleware);
            $app->add($middlewareService);
        }

        return $app;
    }


    /**
     * @param array<string, string> $handlers
     */
    private function registerHandlers(Container $netteContainer, array $handlers): void
    {
        foreach ($handlers as $handlerName => $handlerClass) {
            $this->validateHandlerName($handlerName);

            $handlerService = ServiceProvider::getService($netteContainer, $handlerClass);
            assert(is_callable($handlerService));

            $netteContainer->removeService($handlerName);
            $netteContainer->addService($handlerName, $handlerService);
        }
    }


    private function validateHandlerName(string $handlerName): void
    {
        if (in_array($handlerName, self::ALLOWED_HANDLERS, true)) {
            return;
        }

        throw new LogicException(sprintf(
            '%s handler name is not allowed, available handlers: %s',
            $handlerName,
            implode(', ', self::ALLOWED_HANDLERS),
        ));
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
     * @param bool|array<mixed> $defaultValue
     */
    private function getSlimSettings(string $key, bool|array $defaultValue): mixed
    {
        return $this->configuration[self::SLIM_CONFIGURATION][self::SETTINGS][$key] ?? $defaultValue;
    }


    private function registerSlimServicesInContainer(
        Container $netteContainer,
        NettePsrContainerAdapter $psrContainer
    ): void {
        $userSettings = $this->configuration[self::SLIM_CONFIGURATION][self::SETTINGS] ?? [];
        $settings = new Collection(array_merge(self::DEFAULT_SETTINGS, $userSettings));

        $netteContainer->removeService('request');
        $netteContainer->removeService('response');
        $netteContainer->addService('request', $this->requestFactory->create());
        $netteContainer->addService('response', $this->responseFactory->create());

        if (!$netteContainer->hasService('settings')) {
            $netteContainer->addService('settings', $settings);
            $netteContainer->addService('environment', new Environment($_SERVER));
            $netteContainer->addService('router', $this->router);
            $netteContainer->addService('foundHandler', new RequestResponse());
            $netteContainer->addService('phpErrorHandler', new PhpError($settings['displayErrorDetails']));
            $netteContainer->addService('callableResolver', new CallableResolver($psrContainer));
        }
    }
}
