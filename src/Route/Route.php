<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Route;

use BrandEmbassy\Slim\Request\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface Route
{
    public function __invoke(RequestInterface $request, ResponseInterface $response): ResponseInterface;
}
