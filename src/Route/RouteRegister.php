<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Route;

use BrandEmbassy\Slim\Middleware\AfterRouteMiddlewares;
use BrandEmbassy\Slim\Middleware\BeforeRouteMiddlewares;
use BrandEmbassy\Slim\Middleware\MiddlewareGroups;
use Slim\Interfaces\RouterInterface;
use function array_key_exists;
use function array_keys;
use function array_merge_recursive;
use function is_array;
use function levenshtein;

/**
 * @final
 */
class RouteRegister
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

    /**
     * @var AfterRouteMiddlewares
     */
    private $afterRouteMiddlewares;


    public function __construct(
        RouterInterface $router,
        RouteDefinitionFactory $routeDefinitionFactory,
        UrlPatternResolver $urlPatternResolver,
        BeforeRouteMiddlewares $beforeRouteMiddlewares,
        MiddlewareGroups $middlewareGroups,
        AfterRouteMiddlewares $afterRouteMiddlewares
    ) {
        $this->router = $router;
        $this->routeDefinitionFactory = $routeDefinitionFactory;
        $this->urlPatternResolver = $urlPatternResolver;
        $this->beforeRouteMiddlewares = $beforeRouteMiddlewares;
        $this->middlewareGroups = $middlewareGroups;
        $this->afterRouteMiddlewares = $afterRouteMiddlewares;
    }


    /**
     * @param array<string, mixed|mixed[]> $routeData
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
            $routePattern
        );

        foreach ($routeData as $method => $routeDefinitionData) {
            // Skip non-arrays (e.g., neon unsets or explicit false/null to disable a route)
            if (!is_array($routeDefinitionData)) {
                continue;
            }

            // Backward compatibility: allow singular 'middleware' key
            if (isset($routeDefinitionData['middleware'])
                && !isset($routeDefinitionData[RouteDefinition::MIDDLEWARES])
            ) {
                $routeDefinitionData[RouteDefinition::MIDDLEWARES] = $routeDefinitionData['middleware'];
                unset($routeDefinitionData['middleware']);
            }

            // Skip unregistered/disabled routes or incomplete definitions
            if ($routeDefinitionData === $this->getEmptyRouteDefinitionData()
                || $routeDefinitionData === []
                || !array_key_exists(RouteDefinition::SERVICE, $routeDefinitionData)
            ) {
                continue;
            }

            if ($detectTyposInRouteConfiguration) {
                $this->detectTyposInRouteConfiguration([$apiNamespace, $routePattern, $method], $routeDefinitionData);
            }

            $routeDefinition = $this->routeDefinitionFactory->create($method, $routeDefinitionData);
            $routeName = $routeDefinition->getName() ?? $resolveRoutePath;

            $routeToAdd = $this->router->map([$routeDefinition->getMethod()], $urlPattern, $routeDefinition->getRoute());
            $routeToAdd->setName($routeName);

            foreach ($this->getAllMiddlewares($apiNamespace, $routeDefinition) as $middleware) {
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
            $this->afterRouteMiddlewares->getMiddlewares()
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
