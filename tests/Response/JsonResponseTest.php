<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Response;

use BrandEmbassy\Slim\Response\JsonResponse;
use BrandEmbassy\Slim\Response\Response;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @final
 */
class JsonResponseTest extends TestCase
{
    public function testWritesJsonBody(): void
    {
        $response = JsonResponse::from(new Response(), ['foo' => 'bar']);

        Assert::assertSame('{"foo":"bar"}', (string)$response->getBody());
        Assert::assertSame('application/json', $response->getHeaderLine('Content-Type'));
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
}
