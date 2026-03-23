<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Tools;

use Nette\Utils\Json;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Response;

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


    /**
     * @param mixed[] $data
     */
    public static function createJsonResponseFromScratch(
        array $data,
        int $status,
    ): ResponseInterface {
        return self::createJsonResponse(new Response(), $data, $status);
    }
}
