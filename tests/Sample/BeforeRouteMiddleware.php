<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\Middleware\Middleware;
use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;
use BrandEmbassyTest\Slim\MiddlewareInvocationCounter;

class BeforeRouteMiddleware implements Middleware
{
    public const HEADER_NAME = 'before-route-middleware';


    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $response = $response->withAddedHeader(
            'header-to-be-changed-by-after-route-middleware',
            'initial-value'
        );
        $response = $response->withAddedHeader(
            'processed-by-before-route-middlewares',
            'proof-for-before-route'
        );

        return $next($request, $response);
    }
}
