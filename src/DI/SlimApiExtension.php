<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\DI;

use BrandEmbassy\Slim\SlimApplicationFactory;
use Nette\DI\CompilerExtension;

final class SlimApiExtension extends CompilerExtension
{

    /**
     * @var array
     */
    private $defaults = [
        'apiDefinitionKey' => 'api',
        'slimConfiguration' => [],
    ];

    public function loadConfiguration(): void
    {
        $this->validateConfig($this->defaults);
        $builder = $this->getContainerBuilder();
        $builder->addDefinition($this->prefix('slimApi.factory'))
            ->setFactory(SlimApplicationFactory::class, [$this->config]);
    }

}
