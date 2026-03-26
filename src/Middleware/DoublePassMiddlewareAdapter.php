<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Middleware;

use BrandEmbassy\Slim\Request\RequestDecorator;
use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseDecorator;
use BrandEmbassy\Slim\Response\ResponseInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;
use Slim\Routing\RoutingResults;

/**
 * @final
 *
 * Adapts a legacy double-pass middleware (request, response, next)
 * to the PSR-15 single-pass MiddlewareInterface.
 */
class DoublePassMiddlewareAdapter implements MiddlewareInterface
{
    /**
     * @var callable(RequestInterface, ResponseInterface, callable): PsrResponseInterface
     */
    private $middleware;


    public function __construct(callable $middleware)
    {
        $this->middleware = $middleware;
    }


    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): PsrResponseInterface
    {
        $request = $this->copyRouteArgumentsToAttributes($request);

        $wrappedRequest = $this->wrapRequest($request);
        $wrappedResponse = $this->wrapResponse(new Response());
        $next = new LegacyNextHandler($handler);

        return ($this->middleware)($wrappedRequest, $wrappedResponse, $next);
    }


    /**
     * In Slim 4, route arguments are stored in RoutingResults (set by RoutingMiddleware),
     * not as direct request attributes. Legacy middleware expects route arguments
     * to be accessible via $request->getAttribute('argName'). This method bridges
     * that gap by copying route arguments to direct request attributes.
     */
    private function copyRouteArgumentsToAttributes(ServerRequestInterface $request): ServerRequestInterface
    {
        $routingResults = $request->getAttribute(RouteContext::ROUTING_RESULTS);

        if (!$routingResults instanceof RoutingResults) {
            return $request;
        }

        foreach ($routingResults->getRouteArguments() as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        return $request;
    }


    private function wrapRequest(ServerRequestInterface $request): RequestInterface
    {
        if ($request instanceof RequestInterface) {
            return $request;
        }

        return new RequestDecorator($request);
    }


    private function wrapResponse(PsrResponseInterface $response): ResponseInterface
    {
        if ($response instanceof ResponseInterface) {
            return $response;
        }

        return new ResponseDecorator($response);
    }
}
