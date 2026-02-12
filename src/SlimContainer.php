<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim;

use Nette\DI\Container;
use Nette\DI\MissingServiceException;
use Psr\Container\ContainerInterface;
use function array_key_exists;

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
        if (array_key_exists($id, $this->services)) {
            return $this->services[$id];
        }

        try {
            return $this->netteContainer->getService($id);
        } catch (MissingServiceException $e) {
            throw ServiceNotFoundException::fromPrevious($id, $e);
        }
    }


    /**
     * @param string $id
     */
    public function has($id): bool
    {
        if (array_key_exists($id, $this->services)) {
            return true;
        }

        return $this->netteContainer->hasService($id);
    }


    public function getNetteContainer(): Container
    {
        return $this->netteContainer;
    }
}
