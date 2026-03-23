<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @final
 *
 * Bridges a PSR-15 RequestHandler into a legacy double-pass $next callable.
 * Merges headers accumulated by legacy middleware onto the handler's response.
 */
class LegacyNextHandler
{
    private RequestHandlerInterface $handler;


    public function __construct(RequestHandlerInterface $handler)
    {
        $this->handler = $handler;
    }


    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $handlerResponse = $this->handler->handle($request);

        // Legacy middleware adds headers to $response before calling $next,
        // so we preserve those headers on the response returned by the PSR-15 handler.
        foreach ($response->getHeaders() as $name => $values) {
            $handlerResponse = $handlerResponse->withHeader($name, $values);
        }

        return $handlerResponse;
    }
}
