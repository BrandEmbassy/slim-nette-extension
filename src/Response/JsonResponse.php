<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Response;

use LogicException;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Body;
use function fopen;

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
     * @param mixed[] $data
     *
     * @throws JsonException
     */
    public static function from(
        ResponseInterface $response,
        array $data,
        ?int $status = null,
    ): ResponseInterface {
        $json = Json::encode($data);

        $resource = fopen('php://temp', 'rb+');

        if ($resource === false) {
            throw new LogicException('Failed to open php://temp stream');
        }

        $body = new Body($resource);
        $body->write($json);
        $body->rewind();

        $response = $response
            ->withBody($body)
            ->withHeader('Content-Type', 'application/json');

        return $status === null
            ? $response
            : $response->withStatus($status);
    }
}
