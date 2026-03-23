<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\Middleware\Middleware;
use BrandEmbassyTest\Slim\Tools\ResponseCreatorTestTool;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @final
 */
class GoldenKeyAuthMiddleware implements Middleware
{
    public const ACCESS_TOKEN = 'uber-secret-token-made-of-pure-gold';


    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $token = $request->getHeaderLine('X-Api-Key');

        if ($token !== self::ACCESS_TOKEN) {
            return ResponseCreatorTestTool::createJsonResponse($response, ['error' => 'YOU SHALL NOT PASS!'], 401);
        }

        return $next($request, $response);
    }
}
