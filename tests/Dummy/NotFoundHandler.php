<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Dummy;

use BrandEmbassy\Slim\ErrorHandler;
use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;
use Throwable;

final class NotFoundHandler implements ErrorHandler
{
    public function __invoke(
        RequestInterface $request,
        ResponseInterface $response,
        ?Throwable $e = null
    ): ResponseInterface {
        return $response->withJson(['error' => 'Dummy NotFoundHandler here!'], 404);
    }
}
