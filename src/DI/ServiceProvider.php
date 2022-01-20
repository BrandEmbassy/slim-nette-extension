<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\DI;

use Nette\DI\Container;
use Nette\DI\MissingServiceException;
use function assert;
use function class_exists;

final class ServiceProvider
{
    /**
     * @param class-string<mixed>|string $serviceIdentifier
     *
     * @return object
     */
    public static function getService(Container $container, string $serviceIdentifier)
    {
        try {
            return $container->getByName($serviceIdentifier);
        } catch (MissingServiceException $exception) {
            assert(class_exists($serviceIdentifier));
            return $container->getByType($serviceIdentifier);
        }
    }
}
