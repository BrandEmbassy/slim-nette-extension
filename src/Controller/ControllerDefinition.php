<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Controller;

final class ControllerDefinition
{
    public const SERVICE = 'service';
    public const METHODS = 'methods';

    /**
     * @var string
     */
    private $controllerIdentifier;

    /**
     * @var Controller
     */
    private $controller;

    /**
     * @var array<string, string>
     */
    private $methods;


    /**
     * @param array<string, string> $methods
     */
    public function __construct(string $controllerIdentifier, Controller $controller, array $methods)
    {
        $this->controllerIdentifier = $controllerIdentifier;
        $this->controller = $controller;
        $this->methods = $methods;
    }


    public function getControllerIdentifier(): string
    {
        return $this->controllerIdentifier;
    }


    public function getController(): Controller
    {
        return $this->controller;
    }


    /**
     * @return array<string, string>
     */
    public function getMethods(): array
    {
        return $this->methods;
    }
}
