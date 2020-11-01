<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\Middleware\Middleware;
use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;
use BrandEmbassyTest\Slim\MiddlewareInvocationCounter;

final class InvokeCounterMiddleware implements Middleware
{
    public const HEADER_NAME_PREFIX = 'invoke-counter-';

    /**
     * @var string
     */
    private $ident;


    public function __construct(string $ident)
    {
        $this->ident = $ident;
    }


    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $newResponse = MiddlewareInvocationCounter::invoke(self::getName($this->ident), $response);

        return $next($request, $newResponse);
    }


    public static function getName(string $ident): string
    {
        return self::HEADER_NAME_PREFIX . $ident;
    }
}
