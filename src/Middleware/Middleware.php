<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Middleware;

use BrandEmbassy\Slim\Request\RequestInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

interface Middleware
{
    public function __invoke(RequestInterface $request, PsrResponseInterface $response, callable $next): PsrResponseInterface;
}
