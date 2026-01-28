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
        $psrRequest = $serverRequestFactory->createFromGlobals();

        return new Request($psrRequest);
    }
}
