<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim;

use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;
use Closure;
use LogicException;
use Nette\DI\Container;
use Slim\Collection;
use Slim\Interfaces\RouterInterface;
use Throwable;
use function gettype;
use function is_array;
use function sprintf;

final class SlimApplicationFactory
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var mixed[]
     */
    private $configuration;

    /**
     * @var Container
     */
    private $container;


    /**
     * @param mixed[]         $configuration
     * @param RouterInterface $router
     * @param Container       $container
     */
    public function __construct(array $configuration, RouterInterface $router, Container $container)
    {
        $this->configuration = $configuration;
        $this->container = $container;
        $this->router = $router;
    }


    public function create(): SlimApp
    {
        $slimConfiguration = $this->configuration['slimConfiguration'];
        $slimConfiguration['router'] = $this->router;

        $app = new SlimApp($slimConfiguration);

        $configuration = $this->getConfiguration($this->configuration['apiDefinitionKey']);

        $container = $app->getContainer();

        /** @var Collection $settings */
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
     * @param string $configurationCode
     * @return mixed[]
     */
    private function getConfiguration(string $configurationCode): array
    {
        $configuration = $this->container->getParameters()[$configurationCode];

        if (!is_array($configuration)) {
            throw new LogicException(sprintf('Missing %s configuration', $configurationCode));
        }

        if (isset($configuration['handlers'])) {
            $this->validateConfiguration($configuration, $configurationCode, 'handlers', 'array');
        }

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


    private function removeDefaultSlimErrorHandlers(SlimApp $app): void
    {
        $app->getContainer()['phpErrorHandler'] = static function () {
            return static function (RequestInterface $request, ResponseInterface $response, Throwable $e): void {
                throw $e;
            };
        };
    }


    /**
     * @param SlimApp $app
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


    private function registerBeforeRequestMiddleware(SlimApp $app, string $middleware): void
    {
        $this->registerServiceIntoContainer($app, $middleware);
        $app->add($middleware);
    }
}
