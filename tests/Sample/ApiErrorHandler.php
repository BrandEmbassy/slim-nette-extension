<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\ErrorHandler;
use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;
use Throwable;

final class ApiErrorHandler implements ErrorHandler
{
    public function __invoke(
        RequestInterface $request,
        ResponseInterface $response,
        ?Throwable $e = null
    ): ResponseInterface {
        $error = $e !== null
            ? $e->getMessage()
            : 'Unknown error.';

        return $response->withJson(['error' => $error], 500);
    }
}
