<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;
use BrandEmbassy\Slim\Route\Route;
use LogicException;

final class ErroringAction implements Route
{
    // phpcs:disable
    /**
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param mixed[]           $arguments
     * @return ResponseInterface
     */
    public function __invoke(
        RequestInterface $request,
        ResponseInterface $response,
        array $arguments = []
    ): ResponseInterface {
        throw new LogicException('Error or not to error, that\'s the question!');
    }
    // phpcs:enable
}
