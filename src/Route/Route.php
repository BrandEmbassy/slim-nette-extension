<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Route;

use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;

interface Route
{
    public function __invoke(RequestInterface $request, ResponseInterface $response): ResponseInterface;
}
