<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Middleware;

use BrandEmbassy\Slim\DI\ServiceProvider;
use BrandEmbassy\Slim\Request\Request;
use BrandEmbassy\Slim\Response\Response;
use Nette\DI\Container;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
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


    public function createFromIdentifier(string $middlewareIdentifier): callable
    {
        $container = $this->container;

        return static function (
            ServerRequestInterface $psrRequest,
            RequestHandlerInterface $handler
        ) use (
            $middlewareIdentifier,
            $container
        ): PsrResponseInterface {
            $middleware = ServiceProvider::getService($container, $middlewareIdentifier);
            assert(is_callable($middleware));

            // Wrap PSR-7 request in our Request wrapper for backward compatibility
            $request = new Request($psrRequest);

            // Create a Response wrapper with an empty response
            $responseFactory = new \Slim\Psr7\Factory\ResponseFactory();
            $response = new Response($responseFactory->createResponse());

            // Create a $next callable that wraps the PSR-15 handler
            $next = static function ($req, $res) use ($handler, $psrRequest): PsrResponseInterface {
                // Get the inner PSR request if it's our wrapper
                $innerRequest = $req instanceof Request ? $req->getInnerRequest() : $psrRequest;

                return $handler->handle($innerRequest);
            };

            // Call the old-style middleware
            $result = $middleware($request, $response, $next);

            // Return the inner PSR response if it's our wrapper
            return $result instanceof Response ? $result->getInnerResponse() : $result;
        };
    }


    /**
     * @param string[] $middlewareIdentifiers
     *
     * @return callable[]
     */
    public function createFromIdentifiers(array $middlewareIdentifiers): array
    {
        return array_map(
            fn(string $middlewareIdentifier): callable => $this->createFromIdentifier($middlewareIdentifier),
            $middlewareIdentifiers,
        );
    }
}
