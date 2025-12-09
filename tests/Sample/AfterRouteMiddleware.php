<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\Middleware\Middleware;
use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;

class AfterRouteMiddleware implements Middleware
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        // Let inner layers (route + other middlewares) run first, then override the header
        $response = $next($request, $response);

        return $response->withHeader(
            'header-to-be-changed-by-after-route-middleware',
            'changed-value'
        );
    }
}
