<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Request;

use function sprintf;

final class RequestFieldMissingException extends RequestException
{
    public static function create(string $fieldName): self
    {
        return new self(sprintf('Field "%s" is missing in request body', $fieldName));
    }
}
