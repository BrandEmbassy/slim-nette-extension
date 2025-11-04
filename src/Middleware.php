<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim;

use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;

interface Middleware
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface;
}
