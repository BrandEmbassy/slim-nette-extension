<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim;

use Nette\DI\Container;
use Psr\Container\ContainerInterface;

/**
 * @final
 *
 * PSR-11 container that delegates to Nette DI container
 * with support for registering additional services.
 */
class SlimContainer implements ContainerInterface
{
    private Container $netteContainer;

    /**
     * @var array<string, mixed>
     */
    private array $services = [];


    public function __construct(Container $netteContainer)
    {
        $this->netteContainer = $netteContainer;
    }


    public function set(string $id, mixed $value): void
    {
        $this->services[$id] = $value;
    }


    /**
     * @param string $id
     */
    public function get($id): mixed
    {
        return $this->services[$id] ?? $this->netteContainer->getService($id);
    }


    /**
     * @param string $id
     */
    public function has($id): bool
    {
        if (isset($this->services[$id])) {
            return true;
        }

        return $this->netteContainer->hasService($id);
    }


    public function getNetteContainer(): Container
    {
        return $this->netteContainer;
    }
}
