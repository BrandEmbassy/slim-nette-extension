<?php

namespace BrandEmbassyTest\Slim\Dummy;

use BrandEmbassy\Slim\ErrorHandler;
use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;
use Throwable;

final class ApiErrorHandler implements ErrorHandler
{

    /**
     * @inheritdoc
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, Throwable $e = null)
    {
        $error = $e !== null ? $e->getMessage() : 'Unknown error.';

        return $response->withJson(['error' => $error], 500);
    }

}
