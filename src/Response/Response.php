<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Response;

use GuzzleHttp\Psr7\Response as GuzzleResponse;

/**
 * @final
 */
class Response extends GuzzleResponse implements ResponseInterface
{
}
