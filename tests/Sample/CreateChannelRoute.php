<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassyTest\Slim\Tools\ResponseCreatorTestTool;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @final
 */
class CreateChannelRoute
{
    /**
     * @param array<string, string> $args
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return ResponseCreatorTestTool::createJsonResponse($response, ['status' => 'created'], 201);
    }
}
