<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Middleware;

use BrandEmbassy\Slim\DI\ServiceProvider;
use Nette\DI\Container;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use RuntimeException;
use function array_map;
use function is_callable;
use function sprintf;

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

                if (!is_callable($middleware)) {
                    throw new RuntimeException(sprintf(
                        'Resolved middleware "%s" is not callable.',
                        $middlewareIdentifier,
                    ));
                }

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
