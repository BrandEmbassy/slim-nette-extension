<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Request;

use Slim\Http\Environment;

/**
 * @final
 */
class DefaultRequestFactory implements RequestFactory
{
    public function create(): RequestInterface
    {
        return Request::createFromEnvironment(new Environment($_SERVER));
    }
}
