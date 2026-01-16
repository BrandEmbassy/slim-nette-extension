<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Route;

use BrandEmbassy\Slim\DI\ServiceProvider;
use BrandEmbassy\Slim\Middleware\MiddlewareFactory;
use BrandEmbassy\Slim\Request\Request;
use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\Response;
use BrandEmbassy\Slim\Response\ResponseInterface;
use LogicException;
use Nette\DI\Container;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
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
        // Validate that 'service' key exists
        if (!isset($routeDefinitionData[RouteDefinition::SERVICE])) {
            throw new LogicException(
                'Route definition must contain "' . RouteDefinition::SERVICE . '" key with a service class or name'
            );
        }

        $serviceIdentifier = $routeDefinitionData[RouteDefinition::SERVICE];

        // Create an adapter that converts PSR-7 to our interfaces for backward compatibility
        $route = function (
            ServerRequestInterface $psrRequest,
            PsrResponseInterface $psrResponse
        ) use (
            $serviceIdentifier
        ): PsrResponseInterface {
            $route = $this->getRoute($serviceIdentifier);

            // Wrap PSR-7 request/response in our wrappers
            $request = new Request($psrRequest);
            $response = new Response($psrResponse);

            // Call the old-style route
            $result = $route($request, $response);

            // Return the inner PSR response if it's our wrapper
            return $result instanceof Response ? $result->getInnerResponse() : $result;
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
