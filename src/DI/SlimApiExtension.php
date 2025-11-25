<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\DI;

use BrandEmbassy\Slim\Middleware\AfterRouteMiddlewares;
use BrandEmbassy\Slim\Middleware\BeforeRouteMiddlewares;
use BrandEmbassy\Slim\Middleware\MiddlewareFactory;
use BrandEmbassy\Slim\Middleware\MiddlewareGroups;
use BrandEmbassy\Slim\Request\DefaultRequestFactory;
use BrandEmbassy\Slim\Request\RequestFactory;
use BrandEmbassy\Slim\Response\DefaultResponseFactory;
use BrandEmbassy\Slim\Response\ResponseFactory;
use BrandEmbassy\Slim\Route\OnlyNecessaryRoutesProvider;
use BrandEmbassy\Slim\Route\RouteDefinition;
use BrandEmbassy\Slim\Route\RouteDefinitionFactory;
use BrandEmbassy\Slim\Route\RouteRegister;
use BrandEmbassy\Slim\Route\UrlPatternResolver;
use BrandEmbassy\Slim\SlimApplicationFactory;
use BrandEmbassy\Slim\SlimContainerFactory;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Reference;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\DI\Definitions\Statement;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Slim\Container;
use Slim\Router;
use function array_key_exists;
use function array_merge;
use function array_pop;
use function array_replace_recursive;
use function array_unique;
use function array_values;
use function class_exists;
use function is_array;
use function is_string;
use function ltrim;
use function md5;
use function strpos;

/**
 * @final
 */
class SlimApiExtension extends CompilerExtension
{
    public function getConfigSchema(): Schema
    {
        $routeSchema = [
            RouteDefinition::SERVICE => $this->createServiceExpect(),
            RouteDefinition::MIDDLEWARES => Expect::arrayOf($this->createServiceExpect())
                ->default([]),
            RouteDefinition::MIDDLEWARE_GROUPS => Expect::listOf('string')->default([]),
            RouteDefinition::IGNORE_VERSION_MIDDLEWARE_GROUP => Expect::bool(false),
            RouteDefinition::NAME => Expect::type('string')->default(null),
        ];

        return Expect::structure(
            [
                SlimApplicationFactory::ROUTES => Expect::arrayOf(
                    Expect::arrayOf(
                        Expect::arrayOf(
                            Expect::structure($routeSchema)
                                ->castTo('array')
                                ->otherItems()
                        )
                    )
                ),
                SlimApplicationFactory::HANDLERS => Expect::arrayOf($this->createServiceExpect())->default([]),
                SlimApplicationFactory::BEFORE_REQUEST_MIDDLEWARES => Expect::arrayOf($this->createServiceExpect())
                    ->default([]),
                SlimApplicationFactory::AFTER_REQUEST_MIDDLEWARES => Expect::arrayOf($this->createServiceExpect())
                    ->default([]),
                SlimApplicationFactory::BEFORE_ROUTE_MIDDLEWARES => Expect::arrayOf($this->createServiceExpect())
                    ->default([]),
                SlimApplicationFactory::AFTER_ROUTE_MIDDLEWARES => Expect::arrayOf($this->createServiceExpect())
                    ->default([]),
                SlimApplicationFactory::SLIM_CONFIGURATION => Expect::array()->default([]),
                SlimApplicationFactory::API_PREFIX => Expect::string()->default(''),
                SlimApplicationFactory::MIDDLEWARE_GROUPS => Expect::arrayOf(
                    Expect::arrayOf($this->createServiceExpect())
                        ->default([])
                ),
            ]
        );
    }


    public function loadConfiguration(): void
    {
        $builder = $this->getContainerBuilder();
        $config = (array)$this->config;

        // Allow configuring via container parameters as a fallback (keeps BC with existing tests/config)
        $parameters = $builder->parameters;

        // If a key is either not provided or empty in slimApi config, fill it from container parameters
        // Merge routes from parameters with slimApi routes to allow partial overrides (e.g., unregistering a single route)
        $baseRoutes = $parameters['routes'] ?? [];
        $configuredRoutes = $config[SlimApplicationFactory::ROUTES] ?? [];
        if ($configuredRoutes === [] && $baseRoutes !== []) {
            $config[SlimApplicationFactory::ROUTES] = $baseRoutes;
        } else {
            // Start with base and overlay configured
            $mergedRoutes = array_replace_recursive($baseRoutes, $configuredRoutes);
            // Apply explicit unsets where configured routes specify null (e.g., Neon `key!: null`)
            $this->applyRouteUnsets($mergedRoutes, $configuredRoutes);
            $config[SlimApplicationFactory::ROUTES] = $mergedRoutes;
        }
        $config[SlimApplicationFactory::HANDLERS] = (($config[SlimApplicationFactory::HANDLERS] ?? []) === []) ? ($parameters['api']['handlers'] ?? []) : $config[SlimApplicationFactory::HANDLERS];
        $config[SlimApplicationFactory::BEFORE_REQUEST_MIDDLEWARES] = (($config[SlimApplicationFactory::BEFORE_REQUEST_MIDDLEWARES] ?? []) === []) ? ($parameters['beforeRequestMiddlewares'] ?? []) : $config[SlimApplicationFactory::BEFORE_REQUEST_MIDDLEWARES];
        $config[SlimApplicationFactory::AFTER_REQUEST_MIDDLEWARES] = (($config[SlimApplicationFactory::AFTER_REQUEST_MIDDLEWARES] ?? []) === []) ? ($parameters['afterRequestMiddlewares'] ?? []) : $config[SlimApplicationFactory::AFTER_REQUEST_MIDDLEWARES];
        $config[SlimApplicationFactory::BEFORE_ROUTE_MIDDLEWARES] = (($config[SlimApplicationFactory::BEFORE_ROUTE_MIDDLEWARES] ?? []) === []) ? ($parameters['beforeRouteMiddlewares'] ?? []) : $config[SlimApplicationFactory::BEFORE_ROUTE_MIDDLEWARES];
        $config[SlimApplicationFactory::AFTER_ROUTE_MIDDLEWARES] = (($config[SlimApplicationFactory::AFTER_ROUTE_MIDDLEWARES] ?? []) === []) ? ($parameters['afterRouteMiddlewares'] ?? []) : $config[SlimApplicationFactory::AFTER_ROUTE_MIDDLEWARES];
        $config[SlimApplicationFactory::MIDDLEWARE_GROUPS] = (($config[SlimApplicationFactory::MIDDLEWARE_GROUPS] ?? []) === []) ? ($parameters['middlewareGroups'] ?? []) : $config[SlimApplicationFactory::MIDDLEWARE_GROUPS];

        $builder->addDefinition($this->prefix('urlPatterResolver'))
            ->setFactory(UrlPatternResolver::class, [$config[SlimApplicationFactory::API_PREFIX]]);

        $builder->addDefinition($this->prefix('beforeRouteMiddlewares'))
            ->setFactory(BeforeRouteMiddlewares::class, [$config[SlimApplicationFactory::BEFORE_ROUTE_MIDDLEWARES]]);

        $builder->addDefinition($this->prefix('afterRouteMiddlewares'))
            ->setFactory(AfterRouteMiddlewares::class, [$config[SlimApplicationFactory::AFTER_ROUTE_MIDDLEWARES]]);

        $builder->addDefinition($this->prefix('middlewareGroups'))
            ->setFactory(MiddlewareGroups::class, [$config[SlimApplicationFactory::MIDDLEWARE_GROUPS]]);

        $builder->addDefinition($this->prefix('slimAppFactory'))
            ->setFactory(SlimApplicationFactory::class, [$config]);

        $builder->addDefinition($this->prefix('slimContainerFactory'))
            ->setFactory(SlimContainerFactory::class);

        $builder->addDefinition($this->prefix('slimContainer'))
            ->setType(Container::class)
            ->setFactory(
                [
                    new Reference(SlimContainerFactory::class),
                    'create',
                ],
                [$config[SlimApplicationFactory::SLIM_CONFIGURATION]]
            );

        $builder->addDefinition($this->prefix('routeDefinitionFactory'))
            ->setFactory(RouteDefinitionFactory::class);

        $builder->addDefinition($this->prefix('routeRegister'))
            ->setFactory(RouteRegister::class);

        $builder->addDefinition($this->prefix('requestFactory'))
            ->setType(RequestFactory::class)
            ->setFactory(DefaultRequestFactory::class);

        $builder->addDefinition($this->prefix('responseFactory'))
            ->setType(ResponseFactory::class)
            ->setFactory(DefaultResponseFactory::class);

        $builder->addDefinition($this->prefix('middlewareFactory'))
            ->setFactory(MiddlewareFactory::class);

        $builder->addDefinition($this->prefix('slimRouter'))
            ->setFactory(Router::class);

        $builder->addDefinition($this->prefix('onlyNecessaryRoutesProvider'))
            ->setFactory(OnlyNecessaryRoutesProvider::class);
    }


//    /**
//     * Run after all application services are defined so we can respect explicitly wired middlewares.
//     * Auto-register middlewares referenced by FQCN in routes and global lists, but only when there is
//     * no existing service of the same type (typed or named). This preserves uniqueness for getByType().
//     */
//    public function beforeCompile(): void
//    {
//        $builder = $this->getContainerBuilder();
//
//        // Reconstruct effective config similarly to loadConfiguration(), using container params as fallback
//        $config = (array)$this->config;
//        $parameters = $builder->parameters;
//
//        $baseRoutes = $parameters['routes'] ?? [];
//        $configuredRoutes = $config[SlimApplicationFactory::ROUTES] ?? [];
//        if ($configuredRoutes === [] && $baseRoutes !== []) {
//            $routes = $baseRoutes;
//        } else {
//            $routes = array_replace_recursive($baseRoutes, $configuredRoutes ?? []);
//            $this->applyRouteUnsets($routes, $configuredRoutes ?? []);
//        }
//
//        $beforeRoute = (($config[SlimApplicationFactory::BEFORE_ROUTE_MIDDLEWARES] ?? []) === [])
//            ? ($parameters['beforeRouteMiddlewares'] ?? [])
//            : $config[SlimApplicationFactory::BEFORE_ROUTE_MIDDLEWARES];
//        $afterRoute = (($config[SlimApplicationFactory::AFTER_ROUTE_MIDDLEWARES] ?? []) === [])
//            ? ($parameters['afterRouteMiddlewares'] ?? [])
//            : $config[SlimApplicationFactory::AFTER_ROUTE_MIDDLEWARES];
//
//        $ids = [];
//        $ids = array_merge($ids, $this->collectMiddlewareIdentifiersFromRoutes($routes));
//        $ids = array_merge($ids, $this->normalizeToStringList($beforeRoute));
//        $ids = array_merge($ids, $this->normalizeToStringList($afterRoute));
//        $ids = array_values(array_unique($ids));
//
//        foreach ($ids as $id) {
//            if (!is_string($id)) {
//                continue;
//            }
//            // Only consider fully-qualified class names; named aliases are resolved via getByName()
//            if (strpos($id, '\\') === false) {
//                continue;
//            }
//            if (!class_exists($id)) {
//                continue; // skip unknown classes
//            }
//            // Skip if there is already a service of this type (explicitly wired by the app)
//            if ($this->hasServiceByType($id) || $builder->hasDefinition($id)) {
//                continue;
//            }
//            $builder->addDefinition($this->prefix(md5($id)))
//                ->setType($id);
//        }
//    }


    /**
     * Recursively remove keys from $target when $overrides explicitly sets them to null.
     * This mirrors Neon `key!: null` semantics used in tests to unregister a route.
     *
     * @param mixed[] $target
     * @param mixed[] $overrides
     */
    private function applyRouteUnsets(array &$target, array $overrides): void
    {
        foreach ($overrides as $key => $value) {
            if ($value === null) {
                unset($target[$key]);
                continue;
            }
            if (is_array($value) && isset($target[$key]) && is_array($target[$key])) {
                $this->applyRouteUnsets($target[$key], $value);
            }
        }
    }


    private function createServiceExpect(): Schema
    {
        return Expect::anyOf(Expect::string('string'));
    }

//
//    /**
//     * Recursively collects middleware identifiers from the routes map. Supports both
//     * singular 'middleware' and plural 'middlewares' keys to keep BC with configs.
//     *
//     * @param array<string, mixed> $node
//     *
//     * @return string[]
//     */
//    private function collectMiddlewareIdentifiersFromRoutes(array $node): array
//    {
//        $found = [];
//        foreach ($node as $key => $value) {
//            if (!is_array($value)) {
//                continue;
//            }
//
//            if (array_key_exists(RouteDefinition::MIDDLEWARES, $value)) {
//                $found = array_merge(
//                    $found,
//                    $this->normalizeToStringList($value[RouteDefinition::MIDDLEWARES])
//                );
//            }
//
//            // Backward compatibility: configs may still use singular 'middleware'
//            if (isset($value['middleware'])) {
//                $found = array_merge($found, $this->normalizeToStringList($value['middleware']));
//            }
//
//            // Recurse deeper
//            $found = array_merge($found, $this->collectMiddlewareIdentifiersFromRoutes($value));
//        }
//
//        return $found;
//    }

//
//    /**
//     * Normalizes a mixed value (string|list|nested lists) into a flat list of strings.
//     * Non-string values are ignored.
//     *
//     * @param mixed $value
//     *
//     * @return string[]
//     */
//    private function normalizeToStringList($value): array
//    {
//        if ($value === null) {
//            return [];
//        }
//        if (is_string($value)) {
//            return [$value];
//        }
//        if (!is_array($value)) {
//            return [];
//        }
//
//        $out = [];
//        $stack = [$value];
//        while ($stack !== []) {
//            $current = array_pop($stack);
//            foreach ($current as $v) {
//                if (is_string($v)) {
//                    $out[] = $v;
//                } elseif (is_array($v)) {
//                    $stack[] = $v;
//                }
//            }
//        }
//
//        return $out;
//    }
//
//
//    /**
//     * Checks whether a service of given type is already defined in the builder.
//     */
//    private function hasServiceByType(string $fqcn): bool
//    {
//        $builder = $this->getContainerBuilder();
//        foreach ($builder->getDefinitions() as $def) {
//            if ($this->isDefinitionOfType($def, $fqcn)) {
//                return true;
//            }
//        }
//
//        return false;
//    }


//    /**
//     * True if the definition effectively produces the given FQCN.
//     * Covers both direct type and anonymous `- FQCN` services via factory entity.
//     */
//    private function isDefinitionOfType($def, string $fqcn): bool
//    {
//        if (!$def instanceof ServiceDefinition) {
//             return false;
//        }
//        $fqcn = ltrim($fqcn, '\\');
//
//        $type = $def->getType();
//        if (is_string($type) && ltrim($type, '\\') === $fqcn) {
//            return true;
//        }
//
//        $factory = $def->getFactory();
//        if (!$factory instanceof Statement) {
//            return false;
//        }
//        $entity = $factory->getEntity();
//
//        if (is_string($entity) && ltrim($entity, '\\') === $fqcn) {
//            return true;
//        }
//        if (is_array($entity) && isset($entity[0])) {
//            $first = $entity[0];
//            // Case: first item is a class string
//            if (is_string($first) && ltrim($first, '\\') === $fqcn) {
//                return true;
//            }
//            // Case: first item is a service Reference
//            if ($first instanceof Reference) {
//                $refName = $first->getValue();
//                if (is_string($refName)
//                    && $refName !== ''
//                    && $this->getContainerBuilder()->hasDefinition($refName)
//                ) {
//                    $refDef = $this->getContainerBuilder()->getDefinition($refName);
//                    if ($refDef instanceof ServiceDefinition) {
//                        $refType = $refDef->getType();
//                        if (is_string($refType) && ltrim($refType, '\\') === $fqcn) {
//                            return true;
//                        }
//                    }
//                }
//            }
//        }
//
//        return false;
//    }
}
