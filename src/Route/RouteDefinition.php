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
    private $routeService;

    /**
     * @var callable[]
     */
    private $middlewares;


    /**
     * @param callable[] $middlewares
     */
    public function __construct(string $method, Route $routeService, array $middlewares)
    {
        $this->method = $method;
        $this->routeService = $routeService;
        $this->middlewares = $middlewares;
    }


    public function getMethod(): string
    {
        return $this->method;
    }


    public function getRouteService(): Route
    {
        return $this->routeService;
    }


    /**
     * @return callable[]
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
}
