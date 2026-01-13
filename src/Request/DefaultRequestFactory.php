<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Request;

use Slim\Factory\ServerRequestCreatorFactory;

/**
 * @final
 */
class DefaultRequestFactory implements RequestFactory
{
    public function create(): RequestInterface
    {
        $serverRequestCreator = ServerRequestCreatorFactory::create();
        $serverRequest = $serverRequestCreator->createServerRequestFromGlobals();

        return new Request($serverRequest);
    }
}
