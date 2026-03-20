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
class ApiErrorHandler implements ErrorHandler
{
    public function __invoke(
        RequestInterface $request,
        ResponseInterface $response,
        ?Throwable $exception = null
    ): ResponseInterface {
        $error = $exception !== null
            ? $exception->getMessage()
            : 'Unknown error.';

        return ResponseCreatorTestTool::createJsonResponse($response, ['error' => $error], 500);
    }
}
