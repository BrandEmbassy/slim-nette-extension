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
class InvokeCounterMiddleware implements MiddlewareInterface
{
    public const HEADER_NAME_PREFIX = 'invoke-counter-';

    private string $ident;


    public function __construct(string $ident)
    {
        $this->ident = $ident;
    }


    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $counterValue = MiddlewareInvocationCounter::getNextValue();
        $response = $handler->handle($request);

        return $response->withHeader(self::getName($this->ident), $counterValue);
    }


    public static function getName(string $ident): string
    {
        return self::HEADER_NAME_PREFIX . $ident;
    }
}
