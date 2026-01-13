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
class InvokeCounterMiddleware implements Middleware
{
    public const HEADER_NAME_PREFIX = 'invoke-counter-';

    private string $ident;


    public function __construct(string $ident)
    {
        $this->ident = $ident;
    }


    public function __invoke(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $response = $handler->handle($request);
        $wrappedResponse = new Response($response);
        $newResponse = MiddlewareInvocationCounter::invoke(self::getName($this->ident), $wrappedResponse);

        return $newResponse->getInnerResponse();
    }


    public static function getName(string $ident): string
    {
        return self::HEADER_NAME_PREFIX . $ident;
    }
}
