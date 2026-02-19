<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\Middleware\Middleware;
use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassyTest\Slim\MiddlewareInvocationCounter;
use Psr\Http\Message\ResponseInterface;

/**
 * @final
 */
class BeforeRouteMiddleware implements Middleware
{
    public const HEADER_NAME = 'before-route-middleware';


    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $responseWithCounterHeader = MiddlewareInvocationCounter::invoke(self::HEADER_NAME, $response);

        return $next($request, $responseWithCounterHeader);
    }
}
