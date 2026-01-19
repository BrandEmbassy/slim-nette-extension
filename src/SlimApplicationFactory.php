<?php declare(strict_types = 1);

// Platform Backend Patch Applied - PATCH_VERSION: v8

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

        $routesToRegister = $this->configuration[self::ROUTES];
        if ($registerOnlyNecessaryRoutes) {
            /** @var Request $request */
            $request = $slimContainer->get('request');
            $requestUri = $request->getServerParam('REQUEST_URI');

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

        // Ensure router service always points to slimApi.slimRouter (important for multiple create() calls)
        if ($disableUsingSlimContainer) {
            /** @var Container&ContainerInterface $netteContainer */
            $netteContainer = $this->container;
            if ($netteContainer->hasService('router')) {
                $netteContainer->removeService('router');
            }
            $netteContainer->addService('router', $netteContainer->getService('slimApi.slimRouter'));
        }

        $this->registerHandlers(
            $this->container,
            $slimContainer,
            $this->configuration[self::HANDLERS],
            $disableUsingSlimContainer,
        );

        foreach ($this->configuration[self::BEFORE_REQUEST_MIDDLEWARES] as $middleware) {
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

            $slimContainer[$handlerName] = (static fn() => $handlerService);
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
        // Platform backend uses nested structure: routes[namespace][version/prefix][path][method]
        // Check if we have the nested structure (single key that contains all routes)
        if (count($routes) === 1) {
            $firstKey = array_key_first($routes);
            $firstValue = $routes[$firstKey];

            // If the first value is an array and contains route definitions, it is the nested structure
            if (is_array($firstValue) && !empty($firstValue)) {
                // Check if this looks like routes (paths starting with / or empty string)
                $sampleKey = array_key_first($firstValue);
                if ($sampleKey === '' || strpos($sampleKey, '/') === 0) {
                    // This is the nested structure - use the inner array and append version to namespace
                    if ($firstKey !== '' && $firstKey !== ' ') {
                        $apiNamespace = trim($apiNamespace, '/') . '/' . trim($firstKey, '/');
                    }
                    $routes = $firstValue;
                }
            }
        }

        foreach ($routes as $routePattern => $routeData) {
            // Ensure route definitions have required keys with defaults
            // (Parameters from neon don't go through schema validation defaults)
            if (is_array($routeData)) {
                // Check if we have the flat structure (method keys mixed with definition keys)
                // This happens when routes come from parameters without schema validation
                $httpMethods = ['get', 'post', 'put', 'patch', 'delete', 'options', 'head'];
                $hasHttpMethod = false;
                $hasDefinitionKeys = false;

                foreach (array_keys($routeData) as $key) {
                    if (in_array(strtolower($key), $httpMethods)) {
                        $hasHttpMethod = true;
                    }
                    if (in_array($key, ['service', 'middlewares', 'middleware', 'middlewareGroups'])) {
                        $hasDefinitionKeys = true;
                    }
                }

                // If we have both HTTP methods and definition keys at same level, it's the flat structure
                // We need to restructure it properly
                if ($hasHttpMethod && $hasDefinitionKeys) {
                    $restructuredData = [];
                    $definitionKeys = ['service', 'middlewares', 'middleware', 'middlewareGroups', 'name', 'ignoreVersionMiddlewareGroup', 'public'];

                    foreach ($routeData as $key => $value) {
                        if (in_array(strtolower($key), $httpMethods)) {
                            // Check if the HTTP method value is already an array (contains the actual definition)
                            if (is_array($value) && !empty($value)) {
                                // Use the HTTP method's array value directly as the definition
                                $methodDefinition = $value;
                            } else {
                                // This is an HTTP method - extract the definition from the flat structure
                                $methodDefinition = [];
                                foreach ($definitionKeys as $defKey) {
                                    if (isset($routeData[$defKey])) {
                                        $methodDefinition[$defKey] = $routeData[$defKey];
                                    }
                                }
                            }
                            $restructuredData[$key] = $methodDefinition;
                        }
                    }
                    $routeData = $restructuredData;
                }

                // Now normalize each method definition
                $normalizedRouteData = [];
                foreach ($routeData as $method => $definition) {
                    if (!is_array($definition)) {
                        // Skip non-array definitions
                        continue;
                    }

                    // Skip routes without a service key - they are invalid
                    if (!isset($definition['service'])) {
                        continue;
                    }

                    // Normalize the definition
                    $normalizedDefinition = $definition;
                    $normalizedDefinition['middlewares'] = $definition['middlewares'] ?? $definition['middleware'] ?? [];
                    $normalizedDefinition['middlewareGroups'] = $definition['middlewareGroups'] ?? [];
                    $normalizedDefinition['name'] = $definition['name'] ?? null;
                    $normalizedDefinition['ignoreVersionMiddlewareGroup'] = $definition['ignoreVersionMiddlewareGroup'] ?? false;
                    // Remove old 'middleware' key if it exists
                    unset($normalizedDefinition['middleware']);

                    $normalizedRouteData[$method] = $normalizedDefinition;
                }
                $routeData = $normalizedRouteData;
            }

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


    /**
     * @param Container&ContainerInterface $netteContainer
     */
    private function copyServicesFromSlimContainerToNetteContainer(
        $netteContainer,
        SlimContainer $slimContainer
    ): void {
        $netteContainer->removeService('request');
        $netteContainer->removeService('response');
        $netteContainer->addService('request', $slimContainer->get('request'));
        $netteContainer->addService('response', $slimContainer->get('response'));

        if (!$netteContainer->hasService('settings')) {
            $netteContainer->addService('settings', $slimContainer->get('settings'));
            $netteContainer->addService('environment', $slimContainer->get('environment'));
            // Use slimApi.slimRouter which will have routes registered on it, not Slim container router
            $netteContainer->addService('router', $netteContainer->getService('slimApi.slimRouter'));
            $netteContainer->addService('foundHandler', $slimContainer->get('foundHandler'));
            $netteContainer->addService('phpErrorHandler', $slimContainer->get('phpErrorHandler'));
            $netteContainer->addService('errorHandler', $slimContainer->get('errorHandler'));
            $netteContainer->addService('notFoundHandler', $slimContainer->get('notFoundHandler'));
            $netteContainer->addService('notAllowedHandler', $slimContainer->get('notAllowedHandler'));
            $netteContainer->addService('callableResolver', new CallableResolver($netteContainer));
        }
    }
}
