<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Response;

use JsonException;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Factory\StreamFactory;
use stdClass;
use function json_encode;
use const JSON_THROW_ON_ERROR;

/**
 * @final
 */
class JsonResponse
{
    /**
     * @param mixed[]|stdClass $data
     *
     * @throws JsonException
     */
    public static function from(
        ResponseInterface $response,
        array|stdClass $data,
        ?int $status = null,
        int $encodingOptions = 0,
    ): ResponseInterface {
        $json = json_encode($data, $encodingOptions | JSON_THROW_ON_ERROR);

        $body = (new StreamFactory())->createStream($json);

        $response = $response
            ->withBody($body)
            ->withHeader('Content-Type', 'application/json;charset=utf-8');

        if ($status !== null) {
            $response = $response->withStatus($status);
        }

        return $response;
    }
}
