<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim;

use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;
use Throwable;
use function sprintf;

/**
 * @final
 */
class ServiceNotFoundException extends RuntimeException implements NotFoundExceptionInterface
{
    public function __construct(string $id, Throwable $previous)
    {
        parent::__construct(
            sprintf("Service '%s' not found in container.", $id),
            0,
            $previous,
        );
    }
}
