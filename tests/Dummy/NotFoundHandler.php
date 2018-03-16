<?php

namespace BrandEmbassyTest\Slim\Dummy;

use BrandEmbassy\Slim\ErrorHandler;
use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;
use Exception;
use LogicException;

class NotFoundHandler implements ErrorHandler
{

    /**
     * @inheritdoc
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, Exception $e = null)
    {
        throw new LogicException('Dummy NotFoundHandler here!');
    }

}
