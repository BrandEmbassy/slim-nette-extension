<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Request;

use function sprintf;

final class RouteAttributeMissingException extends RequestException
{
    public static function create(string $routeAttributeName): self
    {
        return new self(sprintf('Route attribute "%s" is missing in request path', $routeAttributeName));
    }
}
