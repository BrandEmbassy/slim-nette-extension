<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\Route\Route;
use BrandEmbassyTest\Slim\Tools\ResponseCreatorTestTool;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @final
 */
class ListChannelsRoute implements Route
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return ResponseCreatorTestTool::createJsonResponse(
            $response,
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
            200,
        );
    }
}
