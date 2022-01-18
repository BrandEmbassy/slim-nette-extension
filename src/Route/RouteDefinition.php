<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Route;

final class RouteDefinition
{
    public const SERVICE = 'service';
    public const MIDDLEWARES = 'middlewares';
    public const MIDDLEWARE_GROUPS = 'middlewareGroups';
    public const NAME = 'name';

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
     * @var string[]
     */
    private $middlewareGroups;

    /**
     * @var string|null
     */
    private $name;


    /**
     * @param callable[] $middlewares
     * @param string[] $middlewareGroups
     */
    public function __construct(
        string $method,
        Route $route,
        array $middlewares,
        array $middlewareGroups,
        ?string $name
    ) {
        $this->method = $method;
        $this->route = $route;
        $this->middlewares = $middlewares;
        $this->middlewareGroups = $middlewareGroups;
        $this->name = $name;
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


    /**
     * @return string[]
     */
    public function getMiddlewareGroups(): array
    {
        return $this->middlewareGroups;
    }


    public function getName(): ?string
    {
        return $this->name;
    }
}
