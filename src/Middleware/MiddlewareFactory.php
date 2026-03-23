<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Middleware;

use BrandEmbassy\Slim\DI\ServiceProvider;
use Nette\DI\Container;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
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
        $container = $this->container;

        return new DoublePassMiddlewareAdapter(
            static function (
                ServerRequestInterface $request,
                ResponseInterface $response,
                callable $next,
            ) use (
                $middlewareIdentifier,
                $container
            ): ResponseInterface {
                $middleware = ServiceProvider::getService($container, $middlewareIdentifier);
                assert(is_callable($middleware));

                return $middleware($request, $response, $next);
            },
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
