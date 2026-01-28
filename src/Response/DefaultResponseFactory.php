<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Response;

use Slim\Psr7\Factory\ResponseFactory as Psr7ResponseFactory;

/**
 * @final
 */
class DefaultResponseFactory implements ResponseFactory
{
    public function create(): ResponseInterface
    {
        $psr7Factory = new Psr7ResponseFactory();
        $psrResponse = $psr7Factory->createResponse();

        return new Response($psrResponse);
    }
}
