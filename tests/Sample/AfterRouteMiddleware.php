<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\Middleware\Middleware;
use BrandEmbassyTest\Slim\MiddlewareInvocationCounter;
use BrandEmbassy\Slim\Response\ResponseInterface;
use BrandEmbassy\Slim\Request\RequestInterface;

/**
 * @final
 */
class AfterRouteMiddleware implements Middleware
{
    public const HEADER_NAME = 'after-route-middleware';


    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $responseWithCounterHeader = MiddlewareInvocationCounter::invoke(self::HEADER_NAME, $response);

        return $next($request, $responseWithCounterHeader);
    }
}
