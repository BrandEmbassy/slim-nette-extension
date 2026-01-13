<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim;

use ArrayAccess;
use Nette\DI\Container;
use Psr\Container\ContainerInterface;
use Slim\Interfaces\RouteCollectorInterface;

/**
 * @final
 * 
 * Compatibility container that provides backward compatibility for Slim 3 style access
 * while using Slim 4.
 */
class CompatibilityContainer implements ContainerInterface, ArrayAccess
{
    private Container $netteContainer;
    private ?SlimApp $app;
    private array $customServices = [];
    private ?RouterCompatibility $router = null;

    public function __construct(Container $netteContainer, ?SlimApp $app = null)
    {
        $this->netteContainer = $netteContainer;
        $this->app = $app;
    }

    public function setApp(SlimApp $app): void
    {
        $this->app = $app;
        // Create router compatibility wrapper
        $routeCollector = $app->getRouteCollector();
        $this->router = new RouterCompatibility(
            $routeCollector,
            $routeCollector->getRouteParser()
        );
    }

    public function get(string $id)
    {
        // Handle special Slim services
        if ($id === 'router' && $this->router !== null) {
            return $this->router;
        }

        if ($id === 'settings' && isset($this->customServices['settings'])) {
            return $this->customServices['settings'];
        }

        if (isset($this->customServices[$id])) {
            return $this->customServices[$id];
        }

        return $this->netteContainer->getService($id);
    }

    public function has(string $id): bool
    {
        if ($id === 'router' || $id === 'settings') {
            return true;
        }

        if (isset($this->customServices[$id])) {
            return true;
        }

        return $this->netteContainer->hasService($id);
    }

    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    public function offsetGet($offset): mixed
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value): void
    {
        $this->customServices[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->customServices[$offset]);
    }

    public function getNetteContainer(): Container
    {
        return $this->netteContainer;
    }
}

