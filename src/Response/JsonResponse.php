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
 */
class JsonResponse
{
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

        $stream = fopen('php://temp', 'r+');

        if ($stream === false) {
            throw new LogicException('Failed to open php://temp stream');
        }

        $body = new Body($stream);
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
