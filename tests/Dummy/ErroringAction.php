<?php

namespace BrandEmbassyTest\Slim\Dummy;

use BrandEmbassy\Slim\ActionHandler;
use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;
use LogicException;

final class ErroringAction implements ActionHandler
{

    /**
     * @inheritdoc
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, array $arguments = [])
    {
        throw new LogicException('Error or not to error, that\'s the question!');
    }

}
