<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\DI;

use Nette\DI\Container;
use Nette\DI\MissingServiceException;

final class ServiceProvider
{
    /**
     * @return object
     */
    public static function getService(Container $container, string $serviceName)
    {
        try {
            return $container->getByName($serviceName);
        } catch (MissingServiceException $exception) {
            return $container->getByType($serviceName);
        }
    }
}
