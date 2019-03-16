<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Dummy;

use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;

/**
 * Intentionally not extending ErrorHandler. Slim does not call this with 3rd param at __invoke method.
 */
final class NotAllowedHandler
{
    public function __invoke(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $response->withJson(['error' => 'Dummy NotAllowedHandler here!'], 405);
    }
}
