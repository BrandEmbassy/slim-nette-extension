<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Tools;

use BrandEmbassy\Slim\Response\ResponseDecorator;
use BrandEmbassy\Slim\Response\ResponseInterface;
use Nette\Utils\Json;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
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
        PsrResponseInterface $response,
        array $data,
        int $status,
    ): ResponseInterface {
        $json = Json::encode($data);
        $body = (new StreamFactory())->createStream($json);

        $result = $response
            ->withBody($body)
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);

        if ($result instanceof ResponseInterface) {
            return $result;
        }

        return new ResponseDecorator($result);
    }
}
