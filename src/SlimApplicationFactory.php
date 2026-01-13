<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim;

use BrandEmbassy\Slim\DI\ServiceProvider;
use BrandEmbassy\Slim\Middleware\MiddlewareFactory;
use BrandEmbassy\Slim\Request\Request;
use BrandEmbassy\Slim\Request\RequestFactory;
use BrandEmbassy\Slim\Response\Response;
use BrandEmbassy\Slim\Response\ResponseFactory;
use BrandEmbassy\Slim\Route\OnlyNecessaryRoutesProvider;
use BrandEmbassy\Slim\Route\RouteRegister;
use LogicException;
use Nette\DI\Container;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\AppFactory;
use Slim\Interfaces\RouteCollectorInterface;
use Throwable;
use function apcu_enabled;
use function assert;
use function implode;
use function in_array;
use function is_callable;
use function is_string;
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
    ];

    /**
     * @var mixed[]
     */
    private array $configuration;

    private Container $container;

    private MiddlewareFactory $middlewareFactory;

    private RouteRegister $routeRegister;

    private OnlyNecessaryRoutesProvider $onlyNecessaryRoutesProvider;

    private RequestFactory $requestFactory;

    private ResponseFactory $responseFactory;

    private ResponseFactoryInterface $psr17ResponseFactory;

    private RouteCollectorInterface $routeCollector;


    /**
     * @param mixed[] $configuration
     */
    public function __construct(
        array $configuration,
        Container $container,
        MiddlewareFactory $middlewareFactory,
        RouteRegister $routeRegister,
        OnlyNecessaryRoutesProvider $onlyNecessaryRoutesProvider,
        RequestFactory $requestFactory,
        ResponseFactory $responseFactory,
        ResponseFactoryInterface $psr17ResponseFactory,
        RouteCollectorInterface $routeCollector
    ) {
        $this->configuration = $configuration;
        $this->container = $container;
        $this->middlewareFactory = $middlewareFactory;
        $this->routeRegister = $routeRegister;
        $this->onlyNecessaryRoutesProvider = $onlyNecessaryRoutesProvider;
        $this->requestFactory = $requestFactory;
        $this->responseFactory = $responseFactory;
        $this->psr17ResponseFactory = $psr17ResponseFactory;
        $this->routeCollector = $routeCollector;
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

        AppFactory::setResponseFactory($this->psr17ResponseFactory);
        AppFactory::setRouteCollector($this->routeCollector);

        /** @var SlimApp $app */
        $app = AppFactory::createFromContainer($this->container);

        // Add routing middleware (required for Slim 4)
        $app->addRoutingMiddleware();

        $routesToRegister = $this->configuration[self::ROUTES];
        if ($registerOnlyNecessaryRoutes) {
            $request = $this->requestFactory->create();
            $requestUri = $request->getServerParam('REQUEST_URI');
            assert($requestUri === null || is_string($requestUri));

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

        $this->registerErrorMiddleware($app, $this->configuration[self::HANDLERS]);

        foreach ($this->configuration[self::BEFORE_REQUEST_MIDDLEWARES] as $middleware) {
            $middlewareService = $this->middlewareFactory->createFromIdentifier($middleware);
            $app->add($middlewareService);
        }

        return $app;
    }


    /**
     * @param array<string, string> $handlers
     */
    private function registerErrorMiddleware(SlimApp $app, array $handlers): void
    {
        // Validate handlers
        foreach ($handlers as $handlerName => $handlerClass) {
            $this->validateHandlerName($handlerName);
        }

        $displayErrorDetails = (bool)$this->getSlimSettings('displayErrorDetails', false);
        $logErrors = (bool)$this->getSlimSettings('logErrors', true);
        $logErrorDetails = (bool)$this->getSlimSettings('logErrorDetails', true);

        $errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, $logErrors, $logErrorDetails);

        if (isset($handlers['errorHandler'])) {
            $errorHandler = ServiceProvider::getService($this->container, $handlers['errorHandler']);
            assert(is_callable($errorHandler));
            $errorMiddleware->setDefaultErrorHandler(
                $this->createErrorHandlerAdapter($errorHandler),
            );
        }

        if (isset($handlers['notFoundHandler'])) {
            $notFoundHandler = ServiceProvider::getService($this->container, $handlers['notFoundHandler']);
            assert(is_callable($notFoundHandler));
            $errorMiddleware->setErrorHandler(
                \Slim\Exception\HttpNotFoundException::class,
                $this->createErrorHandlerAdapter($notFoundHandler),
            );
        }

        if (isset($handlers['notAllowedHandler'])) {
            $notAllowedHandler = ServiceProvider::getService($this->container, $handlers['notAllowedHandler']);
            assert(is_callable($notAllowedHandler));
            $errorMiddleware->setErrorHandler(
                \Slim\Exception\HttpMethodNotAllowedException::class,
                $this->createErrorHandlerAdapter($notAllowedHandler),
            );
        }
    }


    /**
     * Creates an adapter that converts old-style error handlers to Slim 4 signature.
     * Old signature: (RequestInterface $request, ResponseInterface $response, ?Throwable $exception): ResponseInterface
     * New signature: (ServerRequestInterface $request, Throwable $exception, bool $displayErrorDetails, bool $logErrors, bool $logErrorDetails): ResponseInterface
     */
    private function createErrorHandlerAdapter(callable $handler): callable
    {
        $psr17ResponseFactory = $this->psr17ResponseFactory;

        return static function (
            ServerRequestInterface $psrRequest,
            Throwable $exception,
            bool $displayErrorDetails,
            bool $logErrors,
            bool $logErrorDetails
        ) use ($handler, $psr17ResponseFactory): PsrResponseInterface {
            // Wrap PSR-7 request/response in our wrappers for backward compatibility
            $request = new Request($psrRequest);
            $response = new Response($psr17ResponseFactory->createResponse());

            // Call the old-style error handler
            $result = $handler($request, $response, $exception);

            // Return the inner PSR response if it's our wrapper
            return $result instanceof Response ? $result->getInnerResponse() : $result;
        };
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
}
