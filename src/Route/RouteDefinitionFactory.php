<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Route;

use BrandEmbassy\Slim\DI\ServiceProvider;
use BrandEmbassy\Slim\Middleware\MiddlewareFactory;
use BrandEmbassy\Slim\Request\Request;
use BrandEmbassy\Slim\Response\Response;
use LogicException;
use Nette\DI\Container;
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

        // Create PSR-15 compatible route handler that wraps Slim 3 style route
        $factory = $this;
        $route = function (
            ServerRequestInterface $psrRequest,
            \Psr\Http\Message\ResponseInterface $psrResponse,
            array $args
        ) use (
            $routeService,
            $factory
        ): \Psr\Http\Message\ResponseInterface {
            $route = $factory->getRoute($routeService);

            // Wrap PSR-7 request/response in our wrappers for backward compatibility
            $request = new Request($psrRequest);
            $response = new Response($psrResponse);

            // Call Slim 3 style route
            $result = $route($request, $response);

            // Return inner PSR-7 response (result will always be our ResponseInterface)
            return $result->getInnerResponse();
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
