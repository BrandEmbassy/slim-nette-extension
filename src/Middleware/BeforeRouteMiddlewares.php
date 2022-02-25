<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Middleware;

/**
 * @final
 */
class BeforeRouteMiddlewares
{
    /**
     * @var callable[]
     */
    private $middlewares;


    /**
     * @param string[] $beforeRouteMiddlewares
     */
    public function __construct(array $beforeRouteMiddlewares, MiddlewareFactory $middlewareFactory)
    {
        $this->middlewares = $middlewareFactory->createFromIdentifiers($beforeRouteMiddlewares);
    }


    /**
     * @return callable[]
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
}
