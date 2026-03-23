<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @final
 */
class ErrorThrowingRoute
{
    /**
     * @param array<string, string> $args
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        throw new LogicException("Error or not to error, that's the question!");
    }
}
