<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;
use BrandEmbassy\Slim\Route\Route;

final class HelloWorldRoute implements Route
{
    /**
     * @param string[] $arguments
     */
    public function __invoke(
        RequestInterface $request,
        ResponseInterface $response,
        array $arguments = []
    ): ResponseInterface {
        return $response->withJson(['Hello World']);
    }
}
