<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassyTest\Slim\Tools\ResponseCreatorTestTool;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * @final
 */
class ApiErrorHandler
{
    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails,
    ): ResponseInterface {
        return ResponseCreatorTestTool::createJsonResponseFromScratch(
            ['error' => $exception->getMessage()],
            500,
        );
    }
}
