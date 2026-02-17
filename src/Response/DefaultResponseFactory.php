<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Response;

use Slim\Http\Response as SlimResponse;

/**
 * @final
 */
class DefaultResponseFactory implements ResponseFactory
{
    public function create(): ResponseInterface
    {
        return new Response(new SlimResponse());
    }
}
