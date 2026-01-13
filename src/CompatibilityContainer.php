<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim;

use ArrayAccess;
use Nette\DI\Container;
use Psr\Container\ContainerInterface;

/**
 * @final
 *
 * Compatibility container that provides backward compatibility for Slim 3 style access
 * while using Slim 4.
 *
 * @implements ArrayAccess<string, mixed>
 */
class CompatibilityContainer implements ContainerInterface, ArrayAccess
{
    private Container $netteContainer;

    private ?SlimApp $app = null;

    /**
     * @var array<string, mixed>
     */
    private array $customServices = [];

    private ?RouterCompatibility $router = null;


    public function __construct(Container $netteContainer)
    {
        $this->netteContainer = $netteContainer;
    }


    public function setApp(SlimApp $app): void
    {
        $this->app = $app;
        // Create router compatibility wrapper
        $routeCollector = $app->getRouteCollector();
        $this->router = new RouterCompatibility(
            $routeCollector,
            $routeCollector->getRouteParser(),
        );
    }


    public function get(string $id): mixed
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


    public function offsetExists(mixed $offset): bool
    {
        return $this->has((string)$offset);
    }


    public function offsetGet(mixed $offset): mixed
    {
        return $this->get((string)$offset);
    }


    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->customServices[(string)$offset] = $value;
    }


    public function offsetUnset(mixed $offset): void
    {
        unset($this->customServices[(string)$offset]);
    }


    public function getNetteContainer(): Container
    {
        return $this->netteContainer;
    }
}
