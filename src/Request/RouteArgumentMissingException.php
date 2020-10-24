<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Request;

use function sprintf;

final class RouteArgumentMissingException extends RequestException
{
    public static function create(string $argument): self
    {
        return new self(sprintf('Route argument "%s" is missing in request path', $argument));
    }
}
