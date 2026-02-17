<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Request;

use Slim\Http\Environment;
use Slim\Http\Request as SlimRequest;

/**
 * @final
 */
class DefaultRequestFactory implements RequestFactory
{
    public function create(): RequestInterface
    {
        $slimRequest = SlimRequest::createFromEnvironment(new Environment($_SERVER));

        return new Request($slimRequest);
    }
}
