<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Response;

use JsonException;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Body;
use stdClass;
use function fopen;
use function json_encode;
use const JSON_THROW_ON_ERROR;

/**
 * @final
 *
 * Stateless helper for writing JSON responses via PSR-7.
 * Preferred replacement for the deprecated
 * {@see \BrandEmbassy\Slim\Response\ResponseInterface::withJson()}.
 */
class JsonResponse
{
    private function __construct()
    {
    }


    /**
     * @param T $response
     * @param mixed[]|stdClass $data
     *
     * @return T
     *
     * @throws JsonException
     *
     * @template T of ResponseInterface
     */
    public static function from(
        ResponseInterface $response,
        array|stdClass $data,
        ?int $status = null,
        int $encodingOptions = 0,
    ): ResponseInterface {
        $json = json_encode($data, $encodingOptions | JSON_THROW_ON_ERROR);

        $resource = fopen('php://temp', 'rb+');

        if ($resource === false) {
            throw new LogicException('Failed to open php://temp stream');
        }

        $body = new Body($resource);
        $body->write($json);
        $body->rewind();

        $response = $response
            ->withBody($body)
            ->withHeader('Content-Type', 'application/json;charset=utf-8');

        if ($status !== null) {
            return $response->withStatus($status);
        }

        return $response;
    }
}
