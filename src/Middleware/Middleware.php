<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Middleware;

use BrandEmbassy\Slim\Request\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface Middleware
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface;
}
