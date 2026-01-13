<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\Response\Response;
use BrandEmbassy\Slim\Route\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @final
 */
class ListChannelsRoute implements Route
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $wrappedResponse = new Response($response);

        return $wrappedResponse->withJson(
            [
                [
                    'id' => 1,
                    'name' => 'First channel',
                ],
                [
                    'id' => 2,
                    'name' => 'Second channel',
                ],
            ],
            200
        )->getInnerResponse();
    }
}
