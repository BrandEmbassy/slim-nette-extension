<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim;

use BrandEmbassy\Slim\Request\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

interface ErrorHandler
{
    public function __invoke(
        RequestInterface $request,
        ResponseInterface $response,
        ?Throwable $exception = null
    ): ResponseInterface;
}
