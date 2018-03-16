<?php

namespace BrandEmbassy\Slim;

use BrandEmbassy\Slim\Request\Request;
use BrandEmbassy\Slim\Response\Response;
use Slim\App;

class SlimApp extends App
{

    /**
     * @inheritdoc
     */
    public function run($silent = false)
    {
        $request = new Request($this->getContainer()->get('request'));
        $response = new Response($this->getContainer()->get('response'));
        $response = $this->process($request, $response);

        if (!$silent) {
            $this->respond($response);
        }

        return $response;
    }
}
