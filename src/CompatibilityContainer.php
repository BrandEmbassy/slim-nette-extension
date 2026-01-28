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
 */
class CompatibilityContainer implements ContainerInterface, ArrayAccess
{
    private Container $netteContainer;

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
        // Create router compatibility wrapper
        $routeCollector = $app->getRouteCollector();
        $this->router = new RouterCompatibility(
            $routeCollector,
            $routeCollector->getRouteParser(),
        );
    }


    /**
     * @param string $id
     */
    public function get($id): mixed
    {
        // Handle special Slim services
        if ($id === 'router' && $this->router !== null) {
            return $this->router;
        }

        if ($id === 'settings' && isset($this->customServices['settings'])) {
            return $this->customServices['settings'];
        }

        return $this->customServices[$id] ?? $this->netteContainer->getService($id);
    }


    /**
     * @param string $id
     */
    public function has($id): bool
    {
        if ($id === 'router' || $id === 'settings') {
            return true;
        }

        if (isset($this->customServices[$id])) {
            return true;
        }

        return $this->netteContainer->hasService($id);
    }


    /**
     * @param mixed $offset
     */
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }


    /**
     * @param mixed $offset
     */
    public function offsetGet($offset): mixed
    {
        return $this->get($offset);
    }


    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->customServices[$offset] = $value;
    }


    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->customServices[$offset]);
    }


    public function getNetteContainer(): Container
    {
        return $this->netteContainer;
    }
}
