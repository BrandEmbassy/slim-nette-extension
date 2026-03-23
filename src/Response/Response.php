<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Response;

use Slim\Psr7\Response as SlimResponse;

/**
 * @final
 *
 * Extends Slim 4's PSR-7 Response.
 * All PSR-7 methods are inherited from the parent.
 */
class Response extends SlimResponse implements ResponseInterface
{
}
