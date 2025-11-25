<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Route;

use BrandEmbassy\Slim\Middleware\BeforeRouteMiddlewares;
use BrandEmbassy\Slim\Middleware\MiddlewareGroups;
use Slim\Interfaces\RouterInterface;
use function array_keys;
use function array_merge_recursive;
use function levenshtein;

/**
 * @final
 */
class RouteRegister
{
    private RouterInterface $router;

    private RouteDefinitionFactory $routeDefinitionFactory;

    private UrlPatternResolver $urlPatternResolver;

    private BeforeRouteMiddlewares $beforeRouteMiddlewares;

    private MiddlewareGroups $middlewareGroups;


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
     * @param array<string, array<mixed>> $routeData
     *
     * @phpstan-param array<
     *     string,
     *     array{
     *         service: string|null,
     *         middlewares: array<string>,
     *         middlewareGroups: array<string>,
     *         ignoreVersionMiddlewareGroup: bool,
     *         name: string|null
     *     }
     * > $routeData
     */
    public function register(
        string $apiNamespace,
        string $routePattern,
        array $routeData,
        bool $detectTyposInRouteConfiguration = true
    ): void {
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

            /** @phpstan-var array{
             *     service: string,
             *     middlewares: array<string>,
             *     middlewareGroups: array<string>,
             *     name: string|null,
             *     ignoreVersionMiddlewareGroup: bool
             * } $routeDefinitionData
             */
            $routeDefinition = $this->routeDefinitionFactory->create($method, $routeDefinitionData);

            $routeName = $routeDefinition->getName() ?? $resolveRoutePath;

            $routeToAdd = $this->router->map(
                [$routeDefinition->getMethod()],
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

        return array_merge_recursive(
            $routeDefinition->getMiddlewares(),
            $middlewaresFromGroups,
            $versionMiddlewares,
            $this->beforeRouteMiddlewares->getMiddlewares(),
        );
    }


    /**
     * @return array<string, mixed>
     *
     * @phpstan-return array{
     *     service: null,
     *     middlewares: array<never>,
     *     middlewareGroups: array<never>,
     *     ignoreVersionMiddlewareGroup: bool,
     *     name: null
     * }
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
