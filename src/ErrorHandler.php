<?php

namespace BrandEmbassy\Slim;

use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;
use Exception;

interface ErrorHandler
{

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param Exception|null $e
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, Exception $e = null);

}
