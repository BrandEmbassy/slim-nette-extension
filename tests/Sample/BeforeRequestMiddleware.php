<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\Middleware\Middleware;
use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;
use BrandEmbassyTest\Slim\MiddlewareInvocationCounter;

class BeforeRequestMiddleware implements Middleware
{
    public const HEADER_NAME = 'before-request-middleware';


    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $response = MiddlewareInvocationCounter::invoke(self::HEADER_NAME, $response);
        // Also add a proof header to assert that before-request middlewares were executed
        $response = $response->withAddedHeader('processed-by-before-request-middleware', 'proof-for-before-request');

        return $next($request, $response);
    }
}
