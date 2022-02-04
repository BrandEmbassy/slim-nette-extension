<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Route;

use RuntimeException;

final class InvalidRouteDefinitionException extends RuntimeException
{
    /**
     * @param string[] $path
     * @param string $hint
     */
    public function __construct(array $path, string $hint)
    {
        parent::__construct(
            sprintf('Unexpected route definition key in "%s", did you mean "%s"?', implode(' › ', $path), $hint)
        );
    }
}