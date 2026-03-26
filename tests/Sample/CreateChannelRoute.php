<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\Route\Route;
use BrandEmbassyTest\Slim\Tools\ResponseCreatorTestTool;
use BrandEmbassy\Slim\Response\ResponseInterface;
use BrandEmbassy\Slim\Request\RequestInterface;

/**
 * @final
 */
class CreateChannelRoute implements Route
{
    public function __invoke(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return ResponseCreatorTestTool::createJsonResponse($response, ['status' => 'created'], 201);
    }
}
