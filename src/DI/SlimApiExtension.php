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
        return Expect::structure(
            [
                'routes' => Expect::arrayOf(
                    Expect::arrayOf(
                        Expect::arrayOf(
                            Expect::structure(
                                [
                                    RouteDefinition::SERVICE => $this->createServiceExpect(),
                                    RouteDefinition::MIDDLEWARES => Expect::arrayOf($this->createServiceExpect())
                                        ->default([]),
                                ]
                            )
                        )
                    )
                ),

                'handlers' => Expect::arrayOf($this->createServiceExpect())->default([]),
                'beforeRequestMiddlewares' => Expect::arrayOf($this->createServiceExpect())->default([]),
                'beforeRouteMiddlewares' => Expect::arrayOf($this->createServiceExpect())->default([]),
                'slimConfiguration' => Expect::array()->default([]),
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
