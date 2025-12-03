<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\Middleware\Middleware;
use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;

class AfterRouteMiddleware implements Middleware
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $response = $response->withHeader(
            'header-to-be-changed-by-after-route-middleware',
            'changed-value'
        );

        return $next($request, $response);
    }
}
