<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Response;

interface ResponseFactory
{
    public function create(): ResponseInterface;
}
