<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\ErrorHandler;
use BrandEmbassy\Slim\Request\RequestInterface;
use Slim\Psr7\Factory\StreamFactory;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use function json_encode;
use const JSON_THROW_ON_ERROR;

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

        $body = (new StreamFactory())->createStream(json_encode(['error' => $error], JSON_THROW_ON_ERROR));

        return $response
            ->withBody($body)
            ->withHeader('Content-Type', 'application/json;charset=utf-8')
            ->withStatus(500);
    }
}
