<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\Middleware\Middleware;
use BrandEmbassyTest\Slim\MiddlewareInvocationCounter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @final
 */
class BeforeRouteMiddleware implements Middleware
{
    public const HEADER_NAME = 'before-route-middleware';


    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $responseWithCounterHeader = MiddlewareInvocationCounter::invoke(self::HEADER_NAME, $response);

        return $next($request, $responseWithCounterHeader);
    }
}
