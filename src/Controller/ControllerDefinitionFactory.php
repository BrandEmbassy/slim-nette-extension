<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Controller;

use BrandEmbassy\Slim\DI\ServiceProvider;
use LogicException;
use Nette\DI\Container;
use function array_keys;
use function assert;
use function method_exists;

final class ControllerDefinitionFactory
{
    /**
     * @var Container
     */
    private $container;


    public function __construct(Container $container)
    {
        $this->container = $container;
    }


    /**
     * @param mixed[] $controllerDefinitionData
     */
    public function create(array $controllerDefinitionData): ControllerDefinition
    {
        $controllerIdentifier = $controllerDefinitionData[ControllerDefinition::SERVICE];
        $controller = $this->getController($controllerIdentifier);

        $actions = array_keys($controllerDefinitionData[ControllerDefinition::METHODS]);

        foreach ($actions as $action) {
            assert(method_exists($controller, (string)$action));
        }

        return new ControllerDefinition(
            $controllerIdentifier,
            $controller,
            $controllerDefinitionData[ControllerDefinition::METHODS]
        );
    }


    private function getController(string $controllerIdentifier): Controller
    {
        $controller = ServiceProvider::getService($this->container, $controllerIdentifier);

        if ($controller instanceof Controller) {
            return $controller;
        }

        throw new LogicException('Defined controller service should implement ' . Controller::class);
    }
}
