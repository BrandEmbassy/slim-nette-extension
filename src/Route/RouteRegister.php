<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Route;

use BrandEmbassy\Slim\Middleware\BeforeRouteMiddlewares;
use BrandEmbassy\Slim\Middleware\MiddlewareGroups;
use Slim\Interfaces\RouterInterface;
use function array_merge_recursive;

final class RouteRegister
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var RouteDefinitionFactory
     */
    private $routeDefinitionFactory;

    /**
     * @var UrlPatternResolver
     */
    private $urlPatternResolver;

    /**
     * @var BeforeRouteMiddlewares
     */
    private $beforeRouteMiddlewares;

    /**
     * @var MiddlewareGroups
     */
    private $middlewareGroups;


    public function __construct(
        RouterInterface $router,
        RouteDefinitionFactory $routeDefinitionFactory,
        UrlPatternResolver $urlPatternResolver,
        BeforeRouteMiddlewares $beforeRouteMiddlewares,
        MiddlewareGroups $middlewareGroups
    ) {
        $this->router = $router;
        $this->routeDefinitionFactory = $routeDefinitionFactory;
        $this->urlPatternResolver = $urlPatternResolver;
        $this->beforeRouteMiddlewares = $beforeRouteMiddlewares;
        $this->middlewareGroups = $middlewareGroups;
    }


    /**
     * @param array<string, mixed[]> $routeData
     */
    public function register(string $apiNamespace, string $routePattern, array $routeData): void
    {
        $urlPattern = $this->urlPatternResolver->resolve($apiNamespace, $routePattern);
        $resolveRoutePath = $this->urlPatternResolver->resolveRoutePath(
            $apiNamespace,
            $routePattern
        );

        foreach ($routeData as $method => $routeDefinitionData) {
            $routeDefinition = $this->routeDefinitionFactory->create($method, $routeDefinitionData);

            $routeName = $routeDefinition->getName() ?? $resolveRoutePath;

            $routeToAdd = $this->router->map(
                [$routeDefinition->getMethod()],
                $urlPattern,
                $routeDefinition->getRoute()
            );
            $routeToAdd->setName($routeName);

            $middlewaresToAdd = $this->getAllMiddlewares($apiNamespace, $routeDefinition);

            foreach ($middlewaresToAdd as $middleware) {
                $routeToAdd->add($middleware);
            }
        }
    }


    /**
     * @return callable[]
     */
    private function getAllMiddlewares(string $version, RouteDefinition $routeDefinition): array
    {
        $versionMiddlewares = $this->middlewareGroups->getMiddlewares($version);
        $middlewaresFromGroups = $this->middlewareGroups->getMiddlewaresForMultipleGroups(
            $routeDefinition->getMiddlewareGroups()
        );

        return array_merge_recursive(
            $routeDefinition->getMiddlewares(),
            $middlewaresFromGroups,
            $versionMiddlewares,
            $this->beforeRouteMiddlewares->getMiddlewares()
        );
    }
}
