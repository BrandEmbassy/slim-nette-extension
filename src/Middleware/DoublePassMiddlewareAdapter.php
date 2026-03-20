<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Middleware;

use BrandEmbassy\Slim\Request\Request;
use BrandEmbassy\Slim\Response\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @final
 *
 * Adapts a legacy double-pass middleware (request, response, next)
 * to the PSR-15 single-pass MiddlewareInterface.
 */
class DoublePassMiddlewareAdapter implements MiddlewareInterface
{
    /**
     * @var callable(Request, Response, callable): ResponseInterface
     */
    private $middleware;


    public function __construct(callable $middleware)
    {
        $this->middleware = $middleware;
    }


    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $wrappedRequest = new Request($request);
        $response = new Response();
        $next = new LegacyNextHandler($handler);

        return ($this->middleware)($wrappedRequest, $response, $next);
    }
}
