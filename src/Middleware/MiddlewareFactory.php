<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Middleware;

use BrandEmbassy\Slim\DI\ServiceProvider;
use BrandEmbassy\Slim\Request\Request;
use BrandEmbassy\Slim\Response\Response;
use BrandEmbassy\Slim\Response\ResponseFactory;
use Nette\DI\Container;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\Route as SlimRoutingRoute;
use function array_map;
use function assert;
use function is_callable;

/**
 * @final
 */
class MiddlewareFactory
{
    private Container $container;

    private ResponseFactory $responseFactory;


    public function __construct(Container $container, ResponseFactory $responseFactory)
    {
        $this->container = $container;
        $this->responseFactory = $responseFactory;
    }


    /**
     * Creates a PSR-15 middleware adapter from a legacy-style middleware identifier.
     */
    public function createFromIdentifier(string $middlewareIdentifier): callable
    {
        $container = $this->container;
        $responseFactory = $this->responseFactory;

        return function (
            ServerRequestInterface $psrRequest,
            RequestHandlerInterface $handler
        ) use (
            $middlewareIdentifier,
            $container,
            $responseFactory
        ): PsrResponseInterface {
            $middleware = ServiceProvider::getService($container, $middlewareIdentifier);
            assert(is_callable($middleware));

            // Bridge Slim 4 route to legacy 'route' attribute for backward compatibility.
            // Always override 'route' with the resolved '__route__' from RoutingMiddleware,
            // because test requests may have a pre-set 'route' attribute with empty arguments.
            $slimRoute = $psrRequest->getAttribute('__route__');
            if ($slimRoute instanceof SlimRoutingRoute) {
                $psrRequest = $psrRequest->withAttribute('route', $slimRoute);
            }

            // Wrap PSR-7 request in our Request wrapper
            $request = new Request($psrRequest);

            // Create a Response wrapper with an empty response
            $response = $responseFactory->create();

            // Create a $next callable that wraps the PSR-15 handler
            $next = function ($req, $res) use ($handler): PsrResponseInterface {
                // Get the inner PSR request if it's our wrapper
                $innerRequest = $req instanceof Request ? $req->getInnerRequest() : $req;

                // Handle the request to get the response from the next layer
                $psrResponse = $handler->handle($innerRequest);

                // If middleware passed a response with headers/state, merge them into the result
                if ($res instanceof Response) {
                    // Get headers from the middleware's response
                    foreach ($res->getHeaders() as $name => $values) {
                        foreach ($values as $value) {
                            $psrResponse = $psrResponse->withAddedHeader($name, $value);
                        }
                    }
                }

                // Wrap PSR-7 response in our Response wrapper
                return new Response($psrResponse);
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
