<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Middleware;

use Psr\Http\Server\MiddlewareInterface;

/**
 * @final
 */
class AfterRouteMiddlewares
{
    /**
     * @var MiddlewareInterface[]
     */
    private array $middlewares;


    /**
     * @param string[] $afterRouteMiddlewares
     */
    public function __construct(array $afterRouteMiddlewares, MiddlewareFactory $middlewareFactory)
    {
        $this->middlewares = $middlewareFactory->createFromIdentifiers($afterRouteMiddlewares);
    }


    /**
     * @return MiddlewareInterface[]
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
}
