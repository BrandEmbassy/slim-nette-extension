<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Tools;

use LogicException;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Slim\Http\Body;
use BrandEmbassy\Slim\Response\ResponseInterface;
use function fopen;

/**
 * @final
 */
class ResponseCreatorTestTool
{
    /**
     * @param mixed[] $data
     *
     * @throws JsonException
     */
    public static function createJsonResponse(
        ResponseInterface $response,
        array $data,
        int $status,
    ): ResponseInterface {
        $json = Json::encode($data);

        $resource = fopen('php://temp', 'rb+');

        if ($resource === false) {
            throw new LogicException('Failed to open php://temp stream');
        }

        $body = new Body($resource);
        $body->write($json);
        $body->rewind();

        return $response
            ->withBody($body)
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
