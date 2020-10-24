<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\Middleware\Middleware;
use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;

final class OnlyApiGroupMiddleware implements Middleware
{
    public const HEADER_NAME = 'only-api-group-middleware';
    public const HEADER_VALUE = 'invoked';


    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $updatedResponse = $response->withHeader(self::HEADER_NAME, self::HEADER_VALUE);

        return $next($request, $updatedResponse);
    }
}
