<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @final
 *
 * Exposes values the route declares (e.g. its permissions) as request attributes; acting on them is up to the application.
 */
class RouteAttributesMiddleware implements MiddlewareInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $attributes;


    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }


    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        foreach ($this->attributes as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        return $handler->handle($request);
    }
}
