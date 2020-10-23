<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Response;

final class DefaultResponseFactory implements ResponseFactory
{
    public function create(): ResponseInterface
    {
        return new Response(new \Slim\Http\Response());
    }
}
