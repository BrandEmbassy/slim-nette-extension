<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Middleware;

/**
 * @final
 */
class AfterRouteMiddlewares
{
    /**
     * @var callable[]
     */
    private $middlewares;


    /**
     * @param string[] $afterRouteMiddlewares
     */
    public function __construct(array $afterRouteMiddlewares, MiddlewareFactory $middlewareFactory)
    {
        $this->middlewares = $middlewareFactory->createFromIdentifiers($afterRouteMiddlewares);
    }


    /**
     * @return callable[]
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
}
