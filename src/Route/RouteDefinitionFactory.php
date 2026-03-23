<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Route;

use BrandEmbassy\Slim\DI\ServiceProvider;
use BrandEmbassy\Slim\Middleware\MiddlewareFactory;
use LogicException;
use Nette\DI\Container;

/**
 * @final
 */
class RouteDefinitionFactory
{
    private Container $container;

    private MiddlewareFactory $middlewareFactory;


    public function __construct(
        Container $container,
        MiddlewareFactory $middlewareFactory
    ) {
        $this->container = $container;
        $this->middlewareFactory = $middlewareFactory;
    }


    /**
     * @param array<string, mixed> $routeDefinitionData
     */
    public function create(string $method, array $routeDefinitionData): RouteDefinition
    {
        $serviceIdentifier = $routeDefinitionData[RouteDefinition::SERVICE];
        $lazyRoute = new LazyRoute(fn(): Route => $this->getRoute($serviceIdentifier));
        $routeHandler = new LegacyRouteAdapter($lazyRoute);

        $middlewares = $this->middlewareFactory->createFromIdentifiers(
            $routeDefinitionData[RouteDefinition::MIDDLEWARES],
        );

        return new RouteDefinition(
            $method,
            $routeHandler,
            $middlewares,
            $routeDefinitionData[RouteDefinition::MIDDLEWARE_GROUPS],
            $routeDefinitionData[RouteDefinition::NAME],
            $routeDefinitionData[RouteDefinition::IGNORE_VERSION_MIDDLEWARE_GROUP],
        );
    }


    private function getRoute(string $routeIdentifier): Route
    {
        $route = ServiceProvider::getService($this->container, $routeIdentifier);

        if ($route instanceof Route) {
            return $route;
        }

        throw new LogicException('Defined route service should implement ' . Route::class);
    }
}
