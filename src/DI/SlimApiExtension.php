<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\DI;

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
use Nette\DI\CompilerExtension;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Psr7\Factory\ResponseFactory as SlimResponseFactory;
use Slim\Routing\RouteCollector;

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
                                ->otherItems(),
                        ),
                    ),
                ),
                SlimApplicationFactory::HANDLERS => Expect::arrayOf($this->createServiceExpect())->default([]),
                SlimApplicationFactory::BEFORE_REQUEST_MIDDLEWARES => Expect::arrayOf($this->createServiceExpect())
                    ->default([]),
                SlimApplicationFactory::BEFORE_ROUTE_MIDDLEWARES => Expect::arrayOf($this->createServiceExpect())
                    ->default([]),
                SlimApplicationFactory::SLIM_CONFIGURATION => Expect::array()->default([]),
                SlimApplicationFactory::API_PREFIX => Expect::string()->default(''),
                SlimApplicationFactory::MIDDLEWARE_GROUPS => Expect::arrayOf(
                    Expect::arrayOf($this->createServiceExpect())
                        ->default([]),
                ),
            ],
        );
    }


    public function loadConfiguration(): void
    {
        $builder = $this->getContainerBuilder();
        $config = (array)$this->config;

        $builder->addDefinition($this->prefix('urlPatterResolver'))
            ->setFactory(UrlPatternResolver::class, [$config[SlimApplicationFactory::API_PREFIX]]);

        $builder->addDefinition($this->prefix('beforeRouteMiddlewares'))
            ->setFactory(BeforeRouteMiddlewares::class, [$config[SlimApplicationFactory::BEFORE_ROUTE_MIDDLEWARES]]);

        $builder->addDefinition($this->prefix('middlewareGroups'))
            ->setFactory(MiddlewareGroups::class, [$config[SlimApplicationFactory::MIDDLEWARE_GROUPS]]);

        $builder->addDefinition($this->prefix('slimAppFactory'))
            ->setFactory(SlimApplicationFactory::class, [$config]);

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

        // PSR-17 Response Factory for Slim 4
        $builder->addDefinition($this->prefix('psr17ResponseFactory'))
            ->setType(ResponseFactoryInterface::class)
            ->setFactory(SlimResponseFactory::class);

        // Route collector for Slim 4
        $builder->addDefinition($this->prefix('routeCollector'))
            ->setType(RouteCollectorInterface::class)
            ->setFactory(RouteCollector::class, ['@' . ResponseFactoryInterface::class]);

        $builder->addDefinition($this->prefix('onlyNecessaryRoutesProvider'))
            ->setFactory(OnlyNecessaryRoutesProvider::class);
    }


    private function createServiceExpect(): Schema
    {
        return Expect::anyOf(Expect::string('string'));
    }
}
