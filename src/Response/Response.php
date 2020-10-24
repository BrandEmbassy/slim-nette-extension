<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Response;

use Slim\Http\Response as SlimResponse;

/**
 * @method static withJson(array $data, ?int $status = null, int $encodingOptions = 0)
 */
final class Response extends SlimResponse implements ResponseInterface
{
}
