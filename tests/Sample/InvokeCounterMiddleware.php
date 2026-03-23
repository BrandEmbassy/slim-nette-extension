<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\Middleware\Middleware;
use BrandEmbassyTest\Slim\MiddlewareInvocationCounter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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


    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $newResponse = MiddlewareInvocationCounter::invoke(self::getName($this->ident), $response);

        return $next($request, $newResponse);
    }


    public static function getName(string $ident): string
    {
        return self::HEADER_NAME_PREFIX . $ident;
    }
}
