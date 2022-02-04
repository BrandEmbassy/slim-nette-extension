<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Route;

use BrandEmbassy\Slim\Middleware\BeforeRouteMiddlewares;
use BrandEmbassy\Slim\Middleware\MiddlewareGroups;
use Slim\Interfaces\RouterInterface;
use function array_keys;
use function array_merge_recursive;
use function levenshtein;

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
            if ($routeDefinitionData === $this->getEmptyRouteDefinitionData()) {
                continue;
            }

            $this->detectTyposInRouteConfiguration([$apiNamespace, $routePattern, $method], $routeDefinitionData);

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
        $versionMiddlewares = $routeDefinition->shouldIgnoreVersionMiddlewareGroup()
            ? []
            : $this->middlewareGroups->getMiddlewares($version);

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


    /**
     * @return array<string, mixed>
     */
    private function getEmptyRouteDefinitionData(): array
    {
        return [
            RouteDefinition::SERVICE => null,
            RouteDefinition::MIDDLEWARES => [],
            RouteDefinition::MIDDLEWARE_GROUPS => [],
            RouteDefinition::IGNORE_VERSION_MIDDLEWARE_GROUP => false,
            RouteDefinition::NAME => null,
        ];
    }


    /**
     * @param string[] $path
     * @param mixed[] $routeDefinitionData
     */
    private function detectTyposInRouteConfiguration(array $path, array $routeDefinitionData): void
    {
        $usedKeys = array_keys($routeDefinitionData);
        foreach ($usedKeys as $usedKey) {
            foreach (RouteDefinition::ALL_DEFINED_KEYS as $definedKey) {
                $levenshteinDistance = levenshtein($usedKey, $definedKey);
                if ($levenshteinDistance > 0 && $levenshteinDistance < 2) {
                    $path[] = $usedKey;

                    throw new InvalidRouteDefinitionException($path, $definedKey);
                }
            }
        }
    }
}
