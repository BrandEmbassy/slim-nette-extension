<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\ErrorHandler;
use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassyTest\Slim\Tools\ResponseCreatorTestTool;
use BrandEmbassy\Slim\Response\ResponseInterface;
use Throwable;

/**
 * @final
 */
class NotFoundHandler implements ErrorHandler
{
    public function __invoke(
        RequestInterface $request,
        ResponseInterface $response,
        ?Throwable $exception = null
    ): ResponseInterface {
        return ResponseCreatorTestTool::createJsonResponse($response, ['error' => 'Sample NotFoundHandler here!'], 404);
    }
}
