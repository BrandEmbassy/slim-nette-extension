<?php

namespace BrandEmbassyTest\Slim\Dummy;

use BrandEmbassy\Slim\ActionHandler;
use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;

class CreateChannelAction implements ActionHandler
{

    /**
     * @inheritdoc
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, array $arguments = [])
    {
        return $response->withJson(['channelId' => 'fb_1234'], 201);
    }

}
