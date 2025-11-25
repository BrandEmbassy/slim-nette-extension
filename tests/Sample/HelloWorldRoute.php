<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;
use BrandEmbassy\Slim\Route\Route;

/**
 * @final
 */
class HelloWorldRoute implements Route
{
    public function __invoke(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $response->withJson(['Hello World']);
    }
}
