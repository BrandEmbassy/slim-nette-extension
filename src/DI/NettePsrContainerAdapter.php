<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\DI;

use Nette\DI\Container;
use Nette\DI\MissingServiceException;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Adapts Nette DI Container to PSR-11 ContainerInterface.
 *
 * Nette\DI\Container does not implement Psr\Container\ContainerInterface natively,
 * but Slim\App requires it. This adapter bridges the two.
 *
 * @final
 */
class NettePsrContainerAdapter implements ContainerInterface
{
    private Container $container;


    public function __construct(Container $container)
    {
        $this->container = $container;
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param string $id
     */
    public function get($id): object
    {
        try {
            return $this->container->getService($id);
        } catch (MissingServiceException $e) {
            throw new class($e->getMessage(), $e->getCode(), $e) extends MissingServiceException implements NotFoundExceptionInterface {
            };
        }
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param string $id
     */
    public function has($id): bool
    {
        return $this->container->hasService($id);
    }


    public function getContainer(): Container
    {
        return $this->container;
    }
}
