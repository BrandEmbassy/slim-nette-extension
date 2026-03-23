<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Route;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface Route
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface;
}
