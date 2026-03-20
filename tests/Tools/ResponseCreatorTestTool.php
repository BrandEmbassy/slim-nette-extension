<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Tools;

use BrandEmbassy\Slim\Response\ResponseInterface;
use Nette\Utils\Json;
use Slim\Psr7\Factory\StreamFactory;

/**
 * @final
 */
class ResponseCreatorTestTool
{
    /**
     * @param mixed[] $data
     */
    public static function createJsonResponse(
        ResponseInterface $response,
        array $data,
        int $status,
    ): ResponseInterface {
        $json = Json::encode($data);
        $body = (new StreamFactory())->createStream($json);

        return $response
            ->withBody($body)
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
