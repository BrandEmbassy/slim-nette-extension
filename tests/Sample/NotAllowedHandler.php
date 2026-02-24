<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;
use BrandEmbassyTest\Slim\Tools\ResponseCreatorTestTool;

/**
 * Intentionally not extending ErrorHandler. Slim does not call this with 3rd param at __invoke method.
 *
 * @final
 */
class NotAllowedHandler
{
    public function __invoke(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return ResponseCreatorTestTool::createJsonResponse($response, ['error' => 'Sample NotAllowedHandler here!'], 405);
    }
}
