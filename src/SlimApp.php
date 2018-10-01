<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim;

use BrandEmbassy\Slim\Request\Request;
use BrandEmbassy\Slim\Response\Response;
use Psr\Http\Message\ResponseInterface;
use Slim\App;

class SlimApp extends App
{

    /**
     * @inheritdoc
     */
    public function run($silent = false): ResponseInterface
    {
        $request = new Request($this->getContainer()->get('request'));
        $response = new Response($this->getContainer()->get('response'));
        $response = $this->process($request, $response);

        $contentTypes = $response->getHeader('Content-Type');
        $contentType = \reset($contentTypes);

        if ($contentType === 'text/html; charset=UTF-8' && $response->getBody()->getSize() === 0) {
            $response = $response->withHeader('Content-Type', 'text/plain; charset=UTF-8');
        }

        if (!$silent) {
            $this->respond($response);
        }

        return $response;
    }

}
