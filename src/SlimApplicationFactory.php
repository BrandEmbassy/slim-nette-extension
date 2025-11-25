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
use Slim\CallableResolver;
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

        $slimContainer = $this->slimContainerFactory->create($slimConfiguration);

        if ($disableUsingSlimContainer) {
            /** @var Container&ContainerInterface $netteContainer */
            $netteContainer = $this->container;
            $this->copyServicesFromSlimContainerToNetteContainer($netteContainer, $slimContainer);
            $app = new SlimApp($netteContainer);
        }

        if (!$disableUsingSlimContainer) {
            $app = new SlimApp($slimContainer);
        }

        /** @var array<string, mixed> $routesToRegister */
        $routesToRegister = $this->configuration[self::ROUTES];
        if ($registerOnlyNecessaryRoutes) {
            /** @var Request $request */
            $request = $slimContainer->get('request');
            $requestUri = $request->getServerParam('REQUEST_URI');

            /** @var array<string, array<string, array<string, mixed>>> $routesToRegister */
            $routesToRegister = $this->onlyNecessaryRoutesProvider->getRoutes(
                $requestUri,
                $routesToRegister,
                $useApcuCache,
                $routeApiNamesAlwaysInclude,
            );
        }

        /** @var array<string, array<string, array<string, mixed>>> $routesToRegister */
        foreach ($routesToRegister as $apiNamespace => $routes) {
            /** @var string $apiNamespace */
            /** @var array<string, array<string, mixed>> $routes */
            $this->registerApi($apiNamespace, $routes, $detectTyposInRouteConfiguration);
        }

        $this->registerHandlers(
            $this->container,
            $slimContainer,
            $this->configuration[self::HANDLERS],
            $disableUsingSlimContainer,
        );

        /** @var string[] $beforeRequestMiddlewares */
        $beforeRequestMiddlewares = $this->configuration[self::BEFORE_REQUEST_MIDDLEWARES];
        foreach ($beforeRequestMiddlewares as $middleware) {
            $middlewareService = $this->middlewareFactory->createFromIdentifier($middleware);
            $app->add($middlewareService);
        }

        return $app;
    }


    /**
     * @param array<string, string> $handlers
     */
    private function registerHandlers(
        Container $netteContainer,
        SlimContainer $slimContainer,
        array $handlers,
        bool $disableUsingSlimContainer
    ): void {
        /** @var array<string, string> $handlers */
        foreach ($handlers as $handlerName => $handlerClass) {
            $this->validateHandlerName($handlerName);
            $handlerService = ServiceProvider::getService($this->container, $handlerClass);
            assert(is_callable($handlerService));

            if ($disableUsingSlimContainer) {
                /** @var Container&ContainerInterface&ArrayAccess<mixed, mixed> $netteContainer */
                unset($netteContainer[$handlerName]);
                $netteContainer[$handlerName] = ServiceProvider::getService($netteContainer, $handlerClass);
                continue;
            }

            $slimContainer[$handlerName] = fn() => $handlerService;
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
    private function registerApi(string $apiNamespace, array $routes, bool $detectTyposInRouteConfiguration): void
    {
        /** @var array<string, array<mixed>> $routes */
        foreach ($routes as $routePattern => $routeData) {
            /** @var string $routePattern */
            /** @var array<mixed> $routeData */
            $this->routeRegister->register($apiNamespace, $routePattern, $routeData, $detectTyposInRouteConfiguration);
        }
    }


    /**
     * @param mixed[] $defaultValue
     *
     * @return mixed
     */
    private function getSlimSettings(string $key, bool|array $defaultValue)
    {
        /** @var array<string, mixed> $settings */
        $settings = $this->configuration[self::SLIM_CONFIGURATION][self::SETTINGS] ?? [];

        return $settings[$key] ?? $defaultValue;
    }


    /**
     * @param Container&ContainerInterface $netteContainer
     */
    private function copyServicesFromSlimContainerToNetteContainer(
        $netteContainer,
        SlimContainer $slimContainer
    ): void {
        $netteContainer->removeService('request');
        $netteContainer->removeService('response');
        /** @var object $req */
        $req = $slimContainer->get('request');
        /** @var object $res */
        $res = $slimContainer->get('response');
        $netteContainer->addService('request', $req);
        $netteContainer->addService('response', $res);

        if (!$netteContainer->hasService('settings')) {
            /** @var object $settings */
            $settings = $slimContainer->get('settings');
            /** @var object $environment */
            $environment = $slimContainer->get('environment');
            /** @var object $router */
            $router = $slimContainer->get('router');
            /** @var object $foundHandler */
            $foundHandler = $slimContainer->get('foundHandler');
            /** @var object $phpErrorHandler */
            $phpErrorHandler = $slimContainer->get('phpErrorHandler');
            /** @var object $errorHandler */
            $errorHandler = $slimContainer->get('errorHandler');
            /** @var object $notFoundHandler */
            $notFoundHandler = $slimContainer->get('notFoundHandler');
            /** @var object $notAllowedHandler */
            $notAllowedHandler = $slimContainer->get('notAllowedHandler');

            $netteContainer->addService('settings', $settings);
            $netteContainer->addService('environment', $environment);
            $netteContainer->addService('router', $router);
            $netteContainer->addService('foundHandler', $foundHandler);
            $netteContainer->addService('phpErrorHandler', $phpErrorHandler);
            $netteContainer->addService('errorHandler', $errorHandler);
            $netteContainer->addService('notFoundHandler', $notFoundHandler);
            $netteContainer->addService('notAllowedHandler', $notAllowedHandler);
            $netteContainer->addService('callableResolver', new CallableResolver($netteContainer));
        }
    }
}
