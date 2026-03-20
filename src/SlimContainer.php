<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim;

use Nette\DI\Container;
use Nette\DI\MissingServiceException;
use Psr\Container\ContainerInterface;

/**
 * @final
 *
 * Thin PSR-11 adapter for Nette DI container.
 * Nette's Container does not implement ContainerInterface,
 * so this wrapper is needed for Slim 4's App constructor.
 */
class SlimContainer implements ContainerInterface
{
    private Container $netteContainer;


    public function __construct(Container $netteContainer)
    {
        $this->netteContainer = $netteContainer;
    }


    /**
     * @param string $id
     */
    public function get($id): mixed
    {
        try {
            return $this->netteContainer->getService($id);
        } catch (MissingServiceException $e) {
            throw new ServiceNotFoundException($id, $e);
        }
    }


    /**
     * @param string $id
     */
    public function has($id): bool
    {
        return $this->netteContainer->hasService($id);
    }


    public function getNetteContainer(): Container
    {
        return $this->netteContainer;
    }
}
