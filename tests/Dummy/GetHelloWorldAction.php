<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Dummy;

use BrandEmbassy\Slim\ActionHandler;
use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;

final class GetHelloWorldAction implements ActionHandler
{
    /**
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param mixed[]           $arguments
     * @return ResponseInterface
     */
    public function __invoke(
        RequestInterface $request,
        ResponseInterface $response,
        array $arguments = []
    ): ResponseInterface {
        return $response->withJson(['Hello World']);
    }
}
