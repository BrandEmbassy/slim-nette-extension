<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim;

use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;
use Closure;
use LogicException;
use Nette\DI\Container;
use Slim\Collection;
use Throwable;
use function gettype;
use function is_array;
use function sprintf;
use function trim;

final class SlimApplicationFactory
{
    /**
     * @var mixed[]
     */
    private $configuration;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var array<Middleware>
     */
    private $beforeRoutesMiddlewares;

    /**
     * @var array<Middleware>
     */
    private $afterRoutesMiddlewares;


    /**
     * @param mixed[]   $configuration
     */
    public function __construct(array $configuration, Container $container)
    {
        $this->configuration = $configuration;
        $this->container = $container;
        $this->beforeRoutesMiddlewares = [];
        $this->afterRoutesMiddlewares = [];
    }


    public function create(): SlimApp
    {
        $app = new SlimApp($this->configuration['slimConfiguration']);

        $configuration = $this->getConfiguration($this->configuration['apiDefinitionKey']);

        $this->registerAfterRouteMiddlewares($app, $configuration);
        $this->registerBeforeRouteMiddlewares($app, $configuration);

        foreach ($configuration['routes'] as $apiName => $api) {
            $this->registerApis($app, $api, $apiName);
        }

        $container = $app->getContainer();

        /** @var Collection<string, mixed> $settings */
        $settings = $container->get('settings');

        if ($settings->get('removeDefaultHandlers') === true) {
            $this->removeDefaultSlimErrorHandlers($app);
        }

        if (isset($configuration['handlers'])) {
            $this->registerHandlers($app, $configuration['handlers']);
        }

        if (isset($configuration['beforeRequestMiddlewares'])) {
            foreach ($configuration['beforeRequestMiddlewares'] as $middleware) {
                $this->registerBeforeRequestMiddleware($app, $middleware);
            }
        }

        return $app;
    }


    /**
     * @return mixed[]
     */
    private function getConfiguration(string $configurationCode): array
    {
        $configuration = $this->container->getParameters()[$configurationCode];

        if (!is_array($configuration)) {
            throw new LogicException(sprintf('Missing %s configuration', $configurationCode));
        }

        $this->validateConfiguration($configuration, $configurationCode, 'routes', 'array');

        if (isset($configuration['handlers'])) {
            $this->validateConfiguration($configuration, $configurationCode, 'handlers', 'array');
        }

        return $configuration;
    }


    /**
     * @param mixed[] $configuration
     */
    private function validateConfiguration(
        array $configuration,
        string $configurationCode,
        string $name,
        string $type
    ): void {
        if (!isset($configuration[$name]) || gettype($configuration[$name]) !== $type) {
            throw new LogicException(
                sprintf(
                    'Missing or empty %s.%s configuration (has to be %s, but is %s)',
                    $configurationCode,
                    $name,
                    $type,
                    gettype($configuration[$name] ?? null)
                )
            );
        }
    }


    /**
     * @return Closure
     */
    private function getServiceProvider(string $serviceName): callable
    {
        return function () use ($serviceName) {
            /** @var object|null $service */
            $service = $this->container->getByType($serviceName, false);

            if ($service === null) {
                $service = $this->container->getService($serviceName);
            }

            return $service;
        };
    }


    private function removeDefaultSlimErrorHandlers(SlimApp $app): void
    {
        $app->getContainer()['phpErrorHandler'] = static function (): callable {
            return static function (RequestInterface $request, ResponseInterface $response, Throwable $exception): void {
                throw $exception;
            };
        };
    }


    /**
     * @param mixed[] $handlers
     */
    private function registerHandlers(SlimApp $app, array $handlers): void
    {
        foreach ($handlers as $handlerName => $handlerClass) {
            $app->getContainer()[$handlerName . 'Handler'] = $this->getServiceProvider($handlerClass);
        }
    }


    private function registerServiceIntoContainer(SlimApp $app, string $serviceName): void
    {
        if (!$app->getContainer()->has($serviceName)) {
            $app->getContainer()[$serviceName] = $this->getServiceProvider($serviceName);
        }
    }


    /**
     * @param mixed[] $api
     */
    private function registerApis(SlimApp $app, array $api, string $apiName): void
    {
        foreach ($api as $version => $routes) {
            $this->registerApi($app, $apiName, $version, $routes);
        }
    }


    /**
     * @param mixed[] $routes
     */
    private function registerApi(SlimApp $app, string $apiName, string $version, array $routes): void
    {
        foreach ($routes as $routeName => $routeData) {
            $urlPattern = $this->createUrlPattern($apiName, $version, $routeName);

            if (isset($routeData['type']) && $routeData['type'] === 'controller') {
                $this->registerControllerRoute($app, $urlPattern, $routeData);
            } else {
                $this->registerInvokableActionRoutes($app, $routeData, $urlPattern);
            }
        }
    }


    /**
     * @deprecated Do not use Controllers, use Invokable Action classes (use MiddleWareInterface)
     *
     * @param mixed[] $routeData
     */
    private function registerControllerRoute(SlimApp $app, string $urlPattern, array $routeData): void
    {
        $this->registerServiceIntoContainer($app, $routeData['service']);

        foreach ($routeData['methods'] as $method => $action) {
            $app->map([$method], $urlPattern, $routeData['service'] . ':' . $action)
                ->add($routeData['service'] . ':middleware');
        }
    }


    /**
     * @param mixed[] $routeData
     */
    private function registerInvokableActionRoutes(SlimApp $app, array $routeData, string $urlPattern): void
    {
        foreach ($routeData as $method => $config) {
            $service = $config['service'];

            $this->registerServiceIntoContainer($app, $service);
            $routeToAdd = $app->map([$method], $urlPattern, $service);

            foreach ($this->afterRoutesMiddlewares as $middleware) {
                $routeToAdd->add($middleware);
            }

            if (isset($config['middleware'])) {
                foreach ($config['middleware'] as $middleware) {
                    $this->registerServiceIntoContainer($app, $middleware);

                    $routeToAdd->add($middleware);
                }
            }

            foreach ($this->beforeRoutesMiddlewares as $middleware) {
                $routeToAdd->add($middleware);
            }
        }
    }


    private function createUrlPattern(string $apiName, string $version, string $routeName): string
    {
        $apiName = trim($apiName, '/');
        $version = trim($version, '/');
        $routeName = trim($routeName, '/');

        if ($version !== '') {
            $version = '/' . $version;
        }

        if ($apiName !== '') {
            $apiName = '/' . $apiName;
        }

        if ($routeName !== '') {
            $routeName = '/' . $routeName;
        }

        return $apiName . $version . $routeName;
    }


    private function registerBeforeRequestMiddleware(SlimApp $app, string $middleware): void
    {
        $this->registerServiceIntoContainer($app, $middleware);
        $app->add($middleware);
    }


    /**
     * @param mixed[] $configuration
     */
    private function registerBeforeRouteMiddlewares(SlimApp $app, array $configuration): void
    {
        if (isset($configuration['beforeRouteMiddlewares'])) {
            foreach ($configuration['beforeRouteMiddlewares'] as $globalMiddleware) {
                $this->registerServiceIntoContainer($app, $globalMiddleware);
                $this->beforeRoutesMiddlewares[] = $app->getContainer()->get($globalMiddleware);
            }
        }
    }


    /**
     * @param mixed[] $configuration
     */
    private function registerAfterRouteMiddlewares(SlimApp $app, array $configuration): void
    {
        if (isset($configuration['afterRouteMiddlewares'])) {
            foreach ($configuration['afterRouteMiddlewares'] as $globalMiddleware) {
                $this->registerServiceIntoContainer($app, $globalMiddleware);
                $this->afterRoutesMiddlewares[] = $app->getContainer()->get($globalMiddleware);
            }
        }
    }
}
