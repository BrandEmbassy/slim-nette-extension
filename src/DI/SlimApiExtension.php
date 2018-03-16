<?php

namespace BrandEmbassy\Slim\DI;

use ArrayObject;
use BrandEmbassy\Slim\SlimApplicationFactory;
use Chadicus\Slim\OAuth2\Middleware\Authorization;
use Nette\DI\CompilerExtension;

final class SlimApiExtension extends CompilerExtension
{

    private $defaults = [
        'apiDefinitionKey' => 'api',
        'slimConfiguration' => [],
    ];

    public function loadConfiguration()
    {
        $this->validateConfig($this->defaults);
        $builder = $this->getContainerBuilder();
        $builder->addDefinition($this->prefix('slimApi.factory'))
            ->setFactory(SlimApplicationFactory::class, [$this->config]);
    }
}
