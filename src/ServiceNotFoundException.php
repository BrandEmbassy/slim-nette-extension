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
    public static function fromPrevious(string $id, Throwable $previous): self
    {
        return new self(
            sprintf("Service '%s' not found in container.", $id),
            0,
            $previous,
        );
    }
}
