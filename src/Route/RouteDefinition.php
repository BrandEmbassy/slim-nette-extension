<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Route;

final class RouteDefinition
{
    public const SERVICE = 'service';
    public const MIDDLEWARES = 'middlewares';

    /**
     * @var string
     */
    private $method;

    /**
     * @var Route
     */
    private $route;

    /**
     * @var callable[]
     */
    private $middlewares;


    /**
     * @param callable[] $middlewares
     */
    public function __construct(string $method, Route $route, array $middlewares)
    {
        $this->method = $method;
        $this->route = $route;
        $this->middlewares = $middlewares;
    }


    public function getMethod(): string
    {
        return $this->method;
    }


    public function getRoute(): Route
    {
        return $this->route;
    }


    /**
     * @return callable[]
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
}
