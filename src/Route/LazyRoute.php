<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Route;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use function assert;

/**
 * @final
 *
 * Lazily resolves a Route from the container on first invocation.
 * This preserves Slim 3 behavior where route services were only
 * instantiated when the matched route was actually invoked.
 */
class LazyRoute implements Route
{
    /**
     * @var (Closure(): Route)|null
     */
    private ?Closure $factory;

    private ?Route $resolvedRoute = null;


    /**
     * @param Closure(): Route $factory
     */
    public function __construct(Closure $factory)
    {
        $this->factory = $factory;
    }


    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if ($this->resolvedRoute === null) {
            assert($this->factory !== null);
            $this->resolvedRoute = ($this->factory)();
            $this->factory = null;
        }

        return ($this->resolvedRoute)($request, $response);
    }
}
