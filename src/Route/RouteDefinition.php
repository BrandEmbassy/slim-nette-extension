<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Route;

final class RouteDefinition
{
    public const SERVICE = 'service';
    public const MIDDLEWARES = 'middlewares';
    public const MIDDLEWARE_GROUPS = 'middlewareGroups';
    public const IGNORE_VERSION_MIDDLEWARE_GROUP = 'ignoreVersionMiddlewareGroup';
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
     * @var bool
     */
    private $ignoreVersionMiddlewareGroup;


    /**
     * @param callable[] $middlewares
     * @param string[] $middlewareGroups
     */
    public function __construct(
        string $method,
        Route $route,
        array $middlewares,
        array $middlewareGroups,
        ?string $name,
        bool $ignoreApiVersionMiddlewareGroup
    ) {
        $this->method = $method;
        $this->route = $route;
        $this->middlewares = $middlewares;
        $this->middlewareGroups = $middlewareGroups;
        $this->name = $name;
        $this->ignoreVersionMiddlewareGroup = $ignoreApiVersionMiddlewareGroup;
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


    public function shouldIgnoreVersionMiddlewareGroup(): bool
    {
        return $this->ignoreVersionMiddlewareGroup;
    }
}
