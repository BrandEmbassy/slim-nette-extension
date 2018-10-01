<?php

namespace BrandEmbassyTest\Slim\Dummy;

use BrandEmbassy\Slim\Middleware;
use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;

class BeforeRouteMiddleware implements Middleware
{

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $response = $response->withAddedHeader(
            'processed-by-before-route-middlewares',
            'proof-for-before-route'
        );

        return $next($request, $response);
    }
}
