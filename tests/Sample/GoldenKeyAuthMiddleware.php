<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\Middleware\Middleware;
use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;

/**
 * @final
 */
class GoldenKeyAuthMiddleware implements Middleware
{
    public const ACCESS_TOKEN = 'uber-secret-token-made-of-pure-gold';


    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $token = $request->getHeaderLine('X-Api-Key');

        if ($token !== self::ACCESS_TOKEN) {
            return $response->withJson(['error' => 'YOU SHALL NOT PASS!'], 401);
        }

        return $next($request, $response);
    }
}
