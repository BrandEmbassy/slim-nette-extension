<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Middleware;

use BrandEmbassy\Slim\DI\ServiceProvider;
use BrandEmbassy\Slim\Request\Request;
use BrandEmbassy\Slim\Response\Response;
use Nette\DI\Container;
use Psr\Http\Message\ResponseInterface;
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


    /**
     * Creates a PSR-15 middleware adapter from a legacy-style middleware identifier.
     */
    public function createFromIdentifier(string $middlewareIdentifier): callable
    {
        $container = $this->container;

        return function (
            ServerRequestInterface $psrRequest,
            RequestHandlerInterface $handler
        ) use (
            $middlewareIdentifier,
            $container
        ): ResponseInterface {
            $middleware = ServiceProvider::getService($container, $middlewareIdentifier);
            assert(is_callable($middleware));

            // Wrap PSR-7 request in our Request wrapper
            $request = new Request($psrRequest);

            // Create a fresh response for the middleware
            $response = new Response();

            // Create a $next callable that wraps the PSR-15 handler
            $next = function (ServerRequestInterface $req, $res) use ($handler): ResponseInterface {
                // Get the inner PSR request if it's our wrapper
                $innerRequest = $req instanceof Request ? $req->getInnerRequest() : $req;

                // Handle the request to get the response from the next layer
                $handlerResponse = $handler->handle($innerRequest);

                // Merge headers accumulated by legacy middleware onto the handler's response
                foreach ($res->getHeaders() as $name => $values) {
                    $handlerResponse = $handlerResponse->withHeader($name, $values);
                }

                return $handlerResponse;
            };

            // Call the old-style middleware
            return $middleware($request, $response, $next);
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
