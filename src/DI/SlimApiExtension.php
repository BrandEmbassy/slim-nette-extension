<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\DI;

use BrandEmbassy\Slim\Route\RouteDefinition;
use BrandEmbassy\Slim\SlimApplicationFactory;
use Nette\DI\CompilerExtension;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

final class SlimApiExtension extends CompilerExtension
{
    public function getConfigSchema(): Schema
    {
        $routeSchema = [
            RouteDefinition::SERVICE => $this->createServiceExpect(),
            RouteDefinition::MIDDLEWARES => Expect::arrayOf($this->createServiceExpect())
                ->default([]),
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
                SlimApplicationFactory::BEFORE_ROUTE_MIDDLEWARES => Expect::arrayOf($this->createServiceExpect())
                    ->default([]),
                SlimApplicationFactory::SLIM_CONFIGURATION => Expect::array()->default([]),
            ]
        );
    }


    public function loadConfiguration(): void
    {
        $builder = $this->getContainerBuilder();
        $builder->addDefinition($this->prefix('slimApi.factory'))
            ->setFactory(SlimApplicationFactory::class, [(array)$this->config]);

        $this->compiler->loadDefinitionsFromConfig(
            $this->loadFromFile(__DIR__ . '/../services.neon')['services']
        );
    }


    private function createServiceExpect(): Schema
    {
        return Expect::anyOf(Expect::string('string'));
    }
}
