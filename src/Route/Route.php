<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Route;

use BrandEmbassy\Slim\Request\RequestInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

interface Route
{
    public function __invoke(RequestInterface $request, PsrResponseInterface $response): PsrResponseInterface;
}
