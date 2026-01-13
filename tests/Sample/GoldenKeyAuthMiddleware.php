<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\Middleware\Middleware;
use BrandEmbassy\Slim\Response\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ResponseFactory;

/**
 * @final
 */
class GoldenKeyAuthMiddleware implements Middleware
{
    public const ACCESS_TOKEN = 'uber-secret-token-made-of-pure-gold';


    public function __invoke(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $token = $request->getHeaderLine('X-Api-Key');

        if ($token !== self::ACCESS_TOKEN) {
            $responseFactory = new ResponseFactory();
            $response = new Response($responseFactory->createResponse());

            return $response->withJson(['error' => 'YOU SHALL NOT PASS!'], 401)->getInnerResponse();
        }

        return $handler->handle($request);
    }
}
