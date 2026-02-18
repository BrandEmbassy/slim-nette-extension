<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\Request\RequestInterface;
use Slim\Psr7\Factory\StreamFactory;
use Psr\Http\Message\ResponseInterface;
use function json_encode;
use const JSON_THROW_ON_ERROR;

/**
 * Intentionally not extending ErrorHandler. Slim does not call this with 3rd param at __invoke method.
 *
 * @final
 */
class NotAllowedHandler
{
    public function __invoke(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = (new StreamFactory())->createStream(json_encode(['error' => 'Sample NotAllowedHandler here!'], JSON_THROW_ON_ERROR));

        return $response
            ->withBody($body)
            ->withHeader('Content-Type', 'application/json;charset=utf-8')
            ->withStatus(405);
    }
}
