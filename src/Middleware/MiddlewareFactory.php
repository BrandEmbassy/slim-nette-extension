<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Middleware;

use BrandEmbassy\Slim\DI\ServiceProvider;
use LogicException;
use Nette\DI\Container;
use Psr\Http\Server\MiddlewareInterface;
use function array_map;

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

        if ($middleware instanceof MiddlewareInterface) {
            return $middleware;
        }

        throw new LogicException(
            'Middleware service "' . $middlewareIdentifier . '" must implement ' . MiddlewareInterface::class,
        );
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
