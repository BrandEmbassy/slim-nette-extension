<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;
use BrandEmbassy\Slim\Route\Route;
use LogicException;

final class ErrorThrowingRoute implements Route
{
    /**
     * @param string[] $arguments
     */
    public function __invoke(
        RequestInterface $request,
        ResponseInterface $response,
        array $arguments = []
    ): ResponseInterface {
        throw new LogicException('Error or not to error, that\'s the question!');
    }
}
