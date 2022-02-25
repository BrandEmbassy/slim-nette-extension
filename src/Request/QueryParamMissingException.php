<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Request;

use function sprintf;

/**
 * @final
 */
class QueryParamMissingException extends RequestException
{
    public static function create(string $key): self
    {
        return new self(sprintf('Query param "%s" is missing in request URI', $key));
    }
}
