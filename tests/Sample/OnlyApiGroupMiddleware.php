<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassyTest\Slim\MiddlewareInvocationCounter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @final
 */
class OnlyApiGroupMiddleware implements MiddlewareInterface
{
    public const HEADER_NAME = 'only-api-group-middleware';


    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $counterValue = MiddlewareInvocationCounter::getNextValue();
        $response = $handler->handle($request);

        return $response->withHeader(self::HEADER_NAME, $counterValue);
    }
}
