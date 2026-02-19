<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Route;

use BrandEmbassy\Slim\DI\ServiceProvider;
use BrandEmbassy\Slim\Middleware\MiddlewareFactory;
use BrandEmbassy\Slim\Request\Request;
use LogicException;
use Nette\DI\Container;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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
        $routeService = $routeDefinitionData[RouteDefinition::SERVICE];

        // Create PSR-15 compatible route handler that wraps the route callable
        $factory = $this;
        $route = function (
            ServerRequestInterface $psrRequest,
            ResponseInterface $psrResponse,
            array $args
        ) use (
            $routeService,
            $factory
        ): ResponseInterface {
            // Note: $args parameter required by Slim 4 route signature but unused in our implementation
            // Route arguments are accessed via RouteContext instead
            unset($args);

            $route = $factory->getRoute($routeService);

            // Wrap PSR-7 request in our Request wrapper
            $request = new Request($psrRequest);

            // Call the route handler with plain PSR-7 response
            return $route($request, $psrResponse);
        };

        $middlewares = $this->middlewareFactory->createFromIdentifiers(
            $routeDefinitionData[RouteDefinition::MIDDLEWARES],
        );

        return new RouteDefinition(
            $method,
            $route,
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
