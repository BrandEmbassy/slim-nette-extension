<?php

namespace BrandEmbassy\Slim;

use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;
use Throwable;

interface ErrorHandler
{

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param Throwable|null $e
     * @return ResponseInterface
     */
    public function __invoke(
        RequestInterface $request,
        ResponseInterface $response,
        Throwable $e = null
    ): ResponseInterface;

}
