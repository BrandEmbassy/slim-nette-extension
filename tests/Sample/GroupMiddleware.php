<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\Middleware\Middleware;
use BrandEmbassyTest\Slim\MiddlewareInvocationCounter;
use BrandEmbassy\Slim\Response\ResponseInterface;
use BrandEmbassy\Slim\Request\RequestInterface;

/**
 * @final
 */
class GroupMiddleware implements Middleware
{
    public const HEADER_NAME = 'group-middleware';


    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $newResponse = MiddlewareInvocationCounter::invoke(self::HEADER_NAME, $response);

        return $next($request, $newResponse);
    }
}
