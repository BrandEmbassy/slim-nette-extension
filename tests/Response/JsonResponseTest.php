<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Response;

use BrandEmbassy\Slim\Response\JsonResponse;
use BrandEmbassy\Slim\Response\Response;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use stdClass;
use function json_decode;
use const JSON_PRETTY_PRINT;

/**
 * @final
 */
class JsonResponseTest extends TestCase
{
    public function testWritesJsonBody(): void
    {
        $response = JsonResponse::from(new Response(), ['foo' => 'bar']);

        Assert::assertSame('{"foo":"bar"}', (string)$response->getBody());
        Assert::assertSame('application/json;charset=utf-8', $response->getHeaderLine('Content-Type'));
    }


    public function testSetsStatusCode(): void
    {
        $response = JsonResponse::from(new Response(), ['created' => true], 201);

        Assert::assertSame(201, $response->getStatusCode());
        Assert::assertSame('{"created":true}', (string)$response->getBody());
    }


    public function testPreservesOriginalStatusWhenNull(): void
    {
        $response = (new Response())->withStatus(204);
        $result = JsonResponse::from($response, ['ok' => true]);

        Assert::assertSame(204, $result->getStatusCode());
    }


    public function testSupportsEncodingOptions(): void
    {
        $response = JsonResponse::from(new Response(), ['foo' => 'bar'], null, JSON_PRETTY_PRINT);

        $expected = "{\n    \"foo\": \"bar\"\n}";
        Assert::assertSame($expected, (string)$response->getBody());
    }


    public function testSupportsStdClass(): void
    {
        $data = new stdClass();
        $data->key = 'value';

        $response = JsonResponse::from(new Response(), $data);

        $decoded = json_decode((string)$response->getBody(), true);
        Assert::assertSame(['key' => 'value'], $decoded);
    }
}
