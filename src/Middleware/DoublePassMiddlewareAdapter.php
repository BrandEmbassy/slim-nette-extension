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

        $next = static function (ServerRequestInterface $req, ResponseInterface $res) use ($handler): ResponseInterface {
            $handlerResponse = $handler->handle($req);

            // Merge headers accumulated by legacy middleware onto the handler's response.
            // Legacy middleware adds headers to $res before calling $next, so we need
            // to preserve those headers on the response returned by the PSR-15 handler.
            foreach ($res->getHeaders() as $name => $values) {
                $handlerResponse = $handlerResponse->withHeader($name, $values);
            }

            return $handlerResponse;
        };

        return ($this->middleware)($wrappedRequest, $response, $next);
    }
}
