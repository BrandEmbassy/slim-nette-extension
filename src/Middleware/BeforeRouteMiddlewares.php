<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Middleware;

use Psr\Http\Server\MiddlewareInterface;

/**
 * @final
 */
class BeforeRouteMiddlewares
{
    /**
     * @var MiddlewareInterface[]
     */
    private array $middlewares;


    /**
     * @param string[] $beforeRouteMiddlewares
     */
    public function __construct(array $beforeRouteMiddlewares, MiddlewareFactory $middlewareFactory)
    {
        $this->middlewares = $middlewareFactory->createFromIdentifiers($beforeRouteMiddlewares);
    }


    /**
     * @return MiddlewareInterface[]
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
}
