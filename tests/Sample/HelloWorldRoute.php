<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\Response\Response;
use BrandEmbassy\Slim\Route\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @final
 */
class HelloWorldRoute implements Route
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $wrappedResponse = new Response($response);

        return $wrappedResponse->withJson(['Hello World'])->getInnerResponse();
    }
}
