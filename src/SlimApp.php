<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\ResponseEmitter;
use Throwable;
use function reset;

class SlimApp extends App
{
    /**
     * @throws Throwable
     */
    public function run(?ServerRequestInterface $request = null): ResponseInterface
    {
        $response = $this->handle($request ?? $this->createRequest());

        $contentTypes = $response->getHeader('Content-Type');
        $contentType = reset($contentTypes);

        if ($contentType === 'text/html; charset=UTF-8' && $response->getBody()->getSize() === 0) {
            $response = $response->withHeader('Content-Type', 'text/plain; charset=UTF-8');
        }

        return $response;
    }


    /**
     * @throws Throwable
     */
    public function runAndEmit(?ServerRequestInterface $request = null): ResponseInterface
    {
        $response = $this->run($request);
        $responseEmitter = new ResponseEmitter();
        $responseEmitter->emit($response);

        return $response;
    }


    private function createRequest(): ServerRequestInterface
    {
        return \Slim\Factory\ServerRequestCreatorFactory::create()->createServerRequestFromGlobals();
    }
}
