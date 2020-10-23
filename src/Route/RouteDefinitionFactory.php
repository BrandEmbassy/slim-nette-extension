<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Route;

use BrandEmbassy\Slim\DI\ServiceProvider;
use BrandEmbassy\Slim\Middleware\MiddlewareFactory;
use LogicException;
use Nette\DI\Container;
use function array_map;

final class RouteDefinitionFactory
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var MiddlewareFactory
     */
    private $middlewareFactory;


    public function __construct(Container $container, MiddlewareFactory $middlewareFactory)
    {
        $this->container = $container;
        $this->middlewareFactory = $middlewareFactory;
    }


    /**
     * @param array<string, mixed> $routeDefinitionData
     */
    public function create(string $method, array $routeDefinitionData): RouteDefinition
    {
        $route = $this->getRoute($routeDefinitionData[RouteDefinition::SERVICE]);
        $middlewares = array_map(
            [$this->middlewareFactory, 'createFromConfig'],
            $routeDefinitionData[RouteDefinition::MIDDLEWARES]
        );

        return new RouteDefinition($method, $route, $middlewares);
    }


    private function getRoute(string $routeService): Route
    {
        $route = ServiceProvider::getService($this->container, $routeService);

        if ($route instanceof Route) {
            return $route;
        }

        throw new LogicException('Defined route service should implement ' . Route::class);
    }
}
