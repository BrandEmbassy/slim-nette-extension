<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Middleware;

use BrandEmbassy\Slim\DI\ServiceProvider;
use Nette\DI\Container;
use function array_map;
use function assert;
use function is_callable;

/**
 * @final
 */
class MiddlewareFactory
{
    /**
     * @var Container
     */
    private $container;


    public function __construct(Container $container)
    {
        $this->container = $container;
    }


    public function createFromIdentifier(string $middlewareIdentifier): callable
    {
        $middleware = ServiceProvider::getService($this->container, $middlewareIdentifier);
        assert(is_callable($middleware));

        return $middleware;
    }


    /**
     * @param string[] $middlewareIdentifiers
     *
     * @return callable[]
     */
    public function createFromIdentifiers(array $middlewareIdentifiers): array
    {
        return array_map(
            function (string $middlewareIdentifier): callable {
                return $this->createFromIdentifier($middlewareIdentifier);
            },
            $middlewareIdentifiers
        );
    }
}
