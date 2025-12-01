<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim;

use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;

interface ActionHandler
{
    /**
     * @param array<string, string> $arguments
     */
    public function __invoke(
        RequestInterface $request,
        ResponseInterface $response,
        array $arguments = []
    ): ResponseInterface;
}
