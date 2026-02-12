<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Route;

use BrandEmbassy\Slim\Middleware\AfterRouteMiddlewares;
use BrandEmbassy\Slim\Middleware\BeforeRouteMiddlewares;
use BrandEmbassy\Slim\Middleware\MiddlewareGroups;
use LogicException;
use Slim\Interfaces\RouteCollectorProxyInterface;
use function array_filter;
use function array_keys;
use function array_merge;
use function levenshtein;
use function strtoupper;

/**
 * @final
 */
class RouteRegister
{
    private RouteDefinitionFactory $routeDefinitionFactory;

    private UrlPatternResolver $urlPatternResolver;

    private BeforeRouteMiddlewares $beforeRouteMiddlewares;

    private AfterRouteMiddlewares $afterRouteMiddlewares;

    private MiddlewareGroups $middlewareGroups;


    public function __construct(
        RouteDefinitionFactory $routeDefinitionFactory,
        UrlPatternResolver $urlPatternResolver,
        BeforeRouteMiddlewares $beforeRouteMiddlewares,
        AfterRouteMiddlewares $afterRouteMiddlewares,
        MiddlewareGroups $middlewareGroups
    ) {
        $this->routeDefinitionFactory = $routeDefinitionFactory;
        $this->urlPatternResolver = $urlPatternResolver;
        $this->beforeRouteMiddlewares = $beforeRouteMiddlewares;
        $this->afterRouteMiddlewares = $afterRouteMiddlewares;
        $this->middlewareGroups = $middlewareGroups;
    }


    /**
     * @param array<string, mixed[]> $routeData
     */
    public function register(
        string $apiNamespace,
        string $routePattern,
        array $routeData,
        bool $detectTyposInRouteConfiguration = true,
        ?RouteCollectorProxyInterface $router = null
    ): void {
        if ($router === null) {
            throw new LogicException('Router must be provided to register routes');
        }

        $urlPattern = $this->urlPatternResolver->resolve($apiNamespace, $routePattern);
        $resolveRoutePath = $this->urlPatternResolver->resolveRoutePath(
            $apiNamespace,
            $routePattern,
        );

        foreach ($routeData as $method => $routeDefinitionData) {
            if ($routeDefinitionData === $this->getEmptyRouteDefinitionData()) {
                continue;
            }

            if ($detectTyposInRouteConfiguration) {
                $this->detectTyposInRouteConfiguration([$apiNamespace, $routePattern, $method], $routeDefinitionData);
            }

            $routeDefinition = $this->routeDefinitionFactory->create($method, $routeDefinitionData);

            $routeName = $routeDefinition->getName() ?? $resolveRoutePath;

            $routeToAdd = $router->map(
                [strtoupper($routeDefinition->getMethod())],
                $urlPattern,
                $routeDefinition->getRoute(),
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
            $routeDefinition->getMiddlewareGroups(),
        );

        $allMiddlewares = array_merge(
            $this->afterRouteMiddlewares->getMiddlewares(),
            $routeDefinition->getMiddlewares(),
            $middlewaresFromGroups,
            $versionMiddlewares,
            $this->beforeRouteMiddlewares->getMiddlewares(),
        );

        // Filter out any null values that might have been introduced
        return array_filter($allMiddlewares, static fn(mixed $middleware): bool => $middleware !== null);
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
