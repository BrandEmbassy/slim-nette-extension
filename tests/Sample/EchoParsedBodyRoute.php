<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;
use BrandEmbassy\Slim\Route\Route;
use BrandEmbassyTest\Slim\Tools\ResponseCreatorTestTool;
use function assert;
use function is_array;

/**
 * @final
 */
class EchoParsedBodyRoute implements Route
{
    public function __invoke(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $parsedBody = $request->getParsedBody();
        assert(is_array($parsedBody));

        return ResponseCreatorTestTool::createJsonResponse($response, $parsedBody, 200);
    }
}
