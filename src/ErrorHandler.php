<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim;

use BrandEmbassy\Slim\Request\RequestInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Throwable;

interface ErrorHandler
{
    public function __invoke(
        RequestInterface $request,
        PsrResponseInterface $response,
        ?Throwable $exception = null
    ): PsrResponseInterface;
}
