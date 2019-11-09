<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim;

use Closure;
use LogicException;
use Nette\DI\Container;
use Slim\Interfaces\RouterInterface;
use Slim\Router;
use function gettype;
use function is_array;
use function sprintf;
use function trim;

final class RouterFactory
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
    private $beforeRoutesMiddlewares = [];


    /**
     * @param mixed[]   $configuration
     * @param Container $container
     */
    public function __construct(array $configuration, Container $container)
    {
        $this->configuration = $configuration;
        $this->container = $container;
    }


    public function create(): RouterInterface
    {
        $configuration = $this->getConfiguration($this->configuration['apiDefinitionKey']);

        $this->registerBeforeRouteMiddlewares($configuration);

        /** @var Router $router */
        $router = new Router();

        foreach ($configuration['routes'] as $apiName => $api) {
            $this->registerApis($router, $api, $apiName);
        }

        return $router;
    }


    /**
     * @param string $configurationCode
     * @return mixed[]
     */
    private function getConfiguration(string $configurationCode): array
    {
        $configuration = $this->container->getParameters()[$configurationCode];

        if (!is_array($configuration)) {
            throw new LogicException(sprintf('Missing %s configuration', $configurationCode));
        }

        $this->validateConfiguration($configuration, $configurationCode, 'routes', 'array');

        return $configuration;
    }


    /**
     * @param mixed[] $configuration
     * @param string  $configurationCode
     * @param string  $name
     * @param string  $type
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
     * @param string $serviceName
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


    /**
     * @param Router  $router
     * @param mixed[] $api
     * @param string  $apiName
     */
    private function registerApis(Router $router, array $api, string $apiName): void
    {
        foreach ($api as $version => $routes) {
            $this->registerApi($router, $apiName, $version, $routes);
        }
    }


    /**
     * @param Router  $router
     * @param string  $apiName
     * @param string  $version
     * @param mixed[] $routes
     */
    private function registerApi(Router $router, string $apiName, string $version, array $routes): void
    {
        foreach ($routes as $routeName => $routeData) {
            $urlPattern = $this->createUrlPattern($apiName, $version, $routeName);

            if (!isset($routeData['type']) || $routeData['type'] !== 'controller') {
                $this->registerInvokableActionRoutes($router, $routeData, $urlPattern);
            }
        }
    }


    /**
     * @param Router  $router
     * @param mixed[] $routeData
     * @param string  $urlPattern
     */
    private function registerInvokableActionRoutes(Router $router, array $routeData, string $urlPattern): void
    {
        foreach ($routeData as $method => $config) {
            $routeToAdd = $router->map([$method], $urlPattern, $this->getServiceProvider($config['service'])($this));

            if (isset($config['middleware'])) {
                foreach ($config['middleware'] as $middleware) {
                    $routeToAdd->add($this->getServiceProvider($middleware)($this));
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


    /**
     * @param mixed[] $configuration
     */
    private function registerBeforeRouteMiddlewares(array $configuration): void
    {
        if (isset($configuration['beforeRouteMiddlewares'])) {
            foreach ($configuration['beforeRouteMiddlewares'] as $globalMiddleware) {
                $this->beforeRoutesMiddlewares[] = $this->getServiceProvider($globalMiddleware)($this);
            }
        }
    }
}
