<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Middleware;

use BrandEmbassy\Slim\DI\ServiceProvider;
use Nette\DI\Container;
use function assert;
use function is_callable;

final class MiddlewareFactory
{
    /**
     * @var Container
     */
    private $container;


    public function __construct(Container $container)
    {
        $this->container = $container;
    }


    public function createFromConfig(string $middlewareIdentifier): callable
    {
        $middleware = ServiceProvider::getService($this->container, $middlewareIdentifier);
        assert(is_callable($middleware));

        return $middleware;
    }
}
