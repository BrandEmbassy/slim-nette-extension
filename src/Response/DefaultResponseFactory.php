<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Response;

use Slim\Psr7\Factory\ResponseFactory as SlimResponseFactory;

/**
 * @final
 */
class DefaultResponseFactory implements ResponseFactory
{
    public function create(): ResponseInterface
    {
        $slimResponseFactory = new SlimResponseFactory();

        return new Response($slimResponseFactory->createResponse());
    }
}
