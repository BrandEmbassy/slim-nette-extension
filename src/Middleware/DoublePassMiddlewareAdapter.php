<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Middleware;

use Psr\Http\Message\ResponseInterface;
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
     * @var callable(ServerRequestInterface, ResponseInterface, callable): ResponseInterface
     */
    private $middleware;


    public function __construct(callable $middleware)
    {
        $this->middleware = $middleware;
    }


    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = self::copyRouteArgumentsToAttributes($request);

        $response = new Response();
        $next = new LegacyNextHandler($handler);

        return ($this->middleware)($request, $response, $next);
    }


    /**
     * In Slim 4, route arguments are stored in RoutingResults (set by RoutingMiddleware),
     * not as direct request attributes. Legacy middleware expects route arguments
     * to be accessible via $request->getAttribute('argName'). This method bridges
     * that gap by copying route arguments to direct request attributes.
     */
    private static function copyRouteArgumentsToAttributes(ServerRequestInterface $request): ServerRequestInterface
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
}
