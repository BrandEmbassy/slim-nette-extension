<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\Middleware\Middleware;
use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;
use BrandEmbassyTest\Slim\MiddlewareInvocationCounter;

final class OnlyApiGroupMiddleware implements Middleware
{
    public const HEADER_NAME = 'only-api-group-middleware';


    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $newResponse = MiddlewareInvocationCounter::invoke(self::HEADER_NAME, $response);

        return $next($request, $newResponse);
    }
}
