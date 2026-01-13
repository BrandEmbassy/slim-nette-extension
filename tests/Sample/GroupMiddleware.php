<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\Middleware\Middleware;
use BrandEmbassy\Slim\Response\Response;
use BrandEmbassyTest\Slim\MiddlewareInvocationCounter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @final
 */
class GroupMiddleware implements Middleware
{
    public const HEADER_NAME = 'group-middleware';


    public function __invoke(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $response = $handler->handle($request);
        $wrappedResponse = new Response($response);
        $newResponse = MiddlewareInvocationCounter::invoke(self::HEADER_NAME, $wrappedResponse);

        return $newResponse->getInnerResponse();
    }
}
