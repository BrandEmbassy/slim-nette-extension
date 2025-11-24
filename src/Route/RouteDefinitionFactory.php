<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Route;

use BrandEmbassy\Slim\DI\ServiceProvider;
use BrandEmbassy\Slim\Middleware\MiddlewareFactory;
use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;
use LogicException;
use Nette\DI\Container;

/**
 * @final
 */
class RouteDefinitionFactory
{
    public function __construct(
        private Container $container,
        private MiddlewareFactory $middlewareFactory
    ) {
    }


    /**
     * @param array<string, mixed> $routeDefinitionData
     *
     * @phpstan-param array{
     *     service: string,
     *     middlewares: array<string>,
     *     middlewareGroups: array<string>,
     *     name: string|null,
     *     ignoreVersionMiddlewareGroup: bool
     * } $routeDefinitionData
     */
    public function create(string $method, array $routeDefinitionData): RouteDefinition
    {
        /** @var string $serviceId */
        $serviceId = $routeDefinitionData[RouteDefinition::SERVICE];
        $route = function (
            RequestInterface $request,
            ResponseInterface $response
        ) use (
            $serviceId
        ): ResponseInterface {
            $route = $this->getRoute($serviceId);

            return $route($request, $response);
        };

        /** @var array<string> $middlewareIds */
        $middlewareIds = $routeDefinitionData[RouteDefinition::MIDDLEWARES];
        $middlewares = $this->middlewareFactory->createFromIdentifiers($middlewareIds);

        /** @var array<string> $middlewareGroups */
        $middlewareGroups = $routeDefinitionData[RouteDefinition::MIDDLEWARE_GROUPS];
        /** @var string|null $name */
        $name = $routeDefinitionData[RouteDefinition::NAME];
        /** @var bool $ignoreVersionGroup */
        $ignoreVersionGroup = $routeDefinitionData[RouteDefinition::IGNORE_VERSION_MIDDLEWARE_GROUP];

        return new RouteDefinition(
            $method,
            $route,
            $middlewares,
            $middlewareGroups,
            $name,
            $ignoreVersionGroup,
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
