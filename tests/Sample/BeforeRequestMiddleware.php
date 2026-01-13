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
        $newResponse = MiddlewareInvocationCounter::invoke(self::HEADER_NAME, $response);

        return $next($request, $newResponse);
    }
}
