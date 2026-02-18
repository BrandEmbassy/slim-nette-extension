<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Route\Route;
use LogicException;
use Psr\Http\Message\ResponseInterface;

/**
 * @final
 */
class ErrorThrowingRoute implements Route
{
    public function __invoke(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        throw new LogicException("Error or not to error, that's the question!");
    }
}
