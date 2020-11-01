<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Request;

use function sprintf;

final class RequestAttributeMissingException extends RequestException
{
    public static function create(string $requestAttributeName): self
    {
        return new self(sprintf('Request attribute "%s" is missing in request', $requestAttributeName));
    }
}
