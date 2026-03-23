<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassyTest\Slim\Tools\ResponseCreatorTestTool;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @final
 */
class GoldenKeyAuthMiddleware implements MiddlewareInterface
{
    public const ACCESS_TOKEN = 'uber-secret-token-made-of-pure-gold';


    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $request->getHeaderLine('X-Api-Key');

        if ($token !== self::ACCESS_TOKEN) {
            return ResponseCreatorTestTool::createJsonResponseFromScratch(['error' => 'YOU SHALL NOT PASS!'], 401);
        }

        return $handler->handle($request);
    }
}
