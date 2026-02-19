<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Tools;

use Psr\Http\Message\ResponseInterface;
use function json_encode;
use const JSON_THROW_ON_ERROR;

/**
 * @final
 */
class JsonResponseTestTool
{
    /**
     * @param mixed[] $data
     */
    public static function from(ResponseInterface $response, array $data, ?int $status = null): ResponseInterface
    {
        $json = json_encode($data, JSON_THROW_ON_ERROR);
        $response->getBody()->write($json);

        $response = $response->withHeader('Content-Type', 'application/json;charset=utf-8');

        if ($status !== null) {
            return $response->withStatus($status);
        }

        return $response;
    }
}
