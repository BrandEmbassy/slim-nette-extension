<?php

namespace BrandEmbassyTest\Slim\Dummy;

use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;
use LogicException;

/**
 * Intentionally not extending ErrorHandler. Slim does not call this with 3rd param at __invoke method.
 */
class NotAllowedHandler
{

    /**
     * @inheritdoc
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response)
    {
        throw new LogicException('Dummy API NotAllowedHandler here!');
    }

}
