<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Factory\ServerRequestCreatorFactory;
use Throwable;
use function reset;

class SlimApp extends App
{
    /**
     * Run the application and return the response.
     *
     * @throws Throwable
     */
    public function runAndReturnResponse(?ServerRequestInterface $request = null): ResponseInterface
    {
        if (!$request instanceof ServerRequestInterface) {
            $serverRequestCreator = ServerRequestCreatorFactory::create();
            $request = $serverRequestCreator->createServerRequestFromGlobals();
        }

        $response = $this->handle($request);

        $contentTypes = $response->getHeader('Content-Type');
        $contentType = reset($contentTypes);

        if ($contentType === 'text/html; charset=UTF-8' && $response->getBody()->getSize() === 0) {
            return $response->withHeader('Content-Type', 'text/plain; charset=UTF-8');
        }

        return $response;
    }
}
