<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Middleware;

use BrandEmbassy\Slim\DI\ServiceProvider;
use Nette\DI\Container;
use Psr\Http\Server\MiddlewareInterface;
use function array_map;
use function assert;
use function is_callable;

/**
 * @final
 */
class MiddlewareFactory
{
    private Container $container;


    public function __construct(Container $container)
    {
        $this->container = $container;
    }


    public function createFromIdentifier(string $middlewareIdentifier): MiddlewareInterface
    {
        $middleware = ServiceProvider::getService($this->container, $middlewareIdentifier);
        assert(is_callable($middleware));

        return new DoublePassMiddlewareAdapter($middleware);
    }


    /**
     * @param string[] $middlewareIdentifiers
     *
     * @return MiddlewareInterface[]
     */
    public function createFromIdentifiers(array $middlewareIdentifiers): array
    {
        return array_map(
            fn(string $middlewareIdentifier): MiddlewareInterface => $this->createFromIdentifier($middlewareIdentifier),
            $middlewareIdentifiers,
        );
    }
}
