<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Route;

use BrandEmbassy\Slim\DI\ServiceProvider;
use BrandEmbassy\Slim\Middleware\MiddlewareFactory;
use BrandEmbassy\Slim\Request\Request;
use BrandEmbassy\Slim\Response\Response;
use LogicException;
use Nette\DI\Container;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Routing\Route as SlimRoutingRoute;

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
            // Route arguments are accessed via Request attributes instead
            unset($args);

            // Bridge Slim 4 route to legacy 'route' attribute for backward compatibility
            $slimRoute = $psrRequest->getAttribute('__route__');
            if ($slimRoute instanceof SlimRoutingRoute) {
                $psrRequest = $psrRequest->withAttribute('route', $slimRoute);
            }

            $route = $factory->getRoute($routeService);

            // Wrap PSR-7 request/response in our wrappers
            $request = new Request($psrRequest);
            $response = new Response($psrResponse);

            // Call the route handler
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
