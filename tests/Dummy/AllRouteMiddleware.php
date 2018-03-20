<?php

namespace BrandEmbassyTest\Slim\Dummy;

use BrandEmbassy\Slim\Middleware;
use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;

class AllRouteMiddleware implements Middleware
{

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        $response = $response->withAddedHeader('processed-by-all-route-middleware', 'correct');

        return $next($request, $response);
    }
}
