<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\Middleware\Middleware;
use BrandEmbassy\Slim\Request\RequestInterface;
use Slim\Psr7\Factory\StreamFactory;
use Psr\Http\Message\ResponseInterface;
use function json_encode;
use const JSON_THROW_ON_ERROR;

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
            $body = (new StreamFactory())->createStream(json_encode(['error' => 'YOU SHALL NOT PASS!'], JSON_THROW_ON_ERROR));

            return $response
                ->withBody($body)
                ->withHeader('Content-Type', 'application/json;charset=utf-8')
                ->withStatus(401);
        }

        return $next($request, $response);
    }
}
