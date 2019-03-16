<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim;

use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;
use Throwable;

interface ErrorHandler
{
    public function __invoke(
        RequestInterface $request,
        ResponseInterface $response,
        ?Throwable $e = null
    ): ResponseInterface;
}
