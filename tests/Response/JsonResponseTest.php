<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Response;

use BrandEmbassy\Slim\Response\JsonResponse;
use BrandEmbassy\Slim\Response\Response;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use stdClass;
use function json_decode;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

/**
 * @final
 */
class JsonResponseTest extends TestCase
{
    public function testFromWithArray(): void
    {
        $data = [
            'foo' => 'bar',
            'baz' => 42,
        ];
        $response = JsonResponse::from(new Response(), $data);

        Assert::assertSame('application/json;charset=utf-8', $response->getHeaderLine('Content-Type'));
        Assert::assertSame(200, $response->getStatusCode());

        $body = (string)$response->getBody();
        Assert::assertSame($data, json_decode($body, true, 512, JSON_THROW_ON_ERROR));
    }


    public function testFromWithStdClass(): void
    {
        $data = new stdClass();
        $data->key = 'value';

        $response = JsonResponse::from(new Response(), $data);

        $body = (string)$response->getBody();
        Assert::assertSame('{"key":"value"}', $body);
    }


    public function testFromWithCustomStatus(): void
    {
        $response = JsonResponse::from(new Response(), ['created' => true], 201);

        Assert::assertSame(201, $response->getStatusCode());
    }


    public function testFromWithEncodingOptions(): void
    {
        $data = ['foo' => 'bar'];
        $response = JsonResponse::from(new Response(), $data, null, JSON_PRETTY_PRINT);

        $expectedJson = <<<'JSON'
{
    "foo": "bar"
}
JSON;
        Assert::assertSame($expectedJson, (string)$response->getBody());
    }
}
