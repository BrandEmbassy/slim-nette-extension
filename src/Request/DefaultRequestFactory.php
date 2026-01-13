<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Request;

use Slim\Psr7\Factory\ServerRequestFactory;

/**
 * @final
 */
class DefaultRequestFactory implements RequestFactory
{
    public function create(): RequestInterface
    {
        $serverRequestFactory = new ServerRequestFactory();
        $serverRequest = $serverRequestFactory->createFromGlobals();
        
        // Create our custom Request from the ServerRequest
        return new Request(
            $serverRequest->getMethod(),
            $serverRequest->getUri(),
            $serverRequest->getHeaders(),
            $serverRequest->getBody(),
            $serverRequest->getProtocolVersion(),
            $serverRequest->getServerParams()
        );
    }
}
