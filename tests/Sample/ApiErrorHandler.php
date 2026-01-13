<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\ErrorHandler;
use BrandEmbassy\Slim\Response\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Throwable;

/**
 * @final
 */
class ApiErrorHandler implements ErrorHandler
{
    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): ResponseInterface {
        $error = $exception->getMessage();

        $responseFactory = new ResponseFactory();
        $response = new Response($responseFactory->createResponse());

        return $response->withJson(['error' => $error], 500);
    }
}
