<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\ErrorHandler;
use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassyTest\Slim\Tools\JsonResponseTestTool;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Throwable;

/**
 * @final
 */
class NotFoundHandler implements ErrorHandler
{
    public function __invoke(
        RequestInterface $request,
        PsrResponseInterface $response,
        ?Throwable $exception = null
    ): PsrResponseInterface {
        return JsonResponseTestTool::from($response, ['error' => 'Sample NotFoundHandler here!'], 404);
    }
}
