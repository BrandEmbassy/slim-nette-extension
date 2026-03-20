<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Tools;

use BrandEmbassy\Slim\Response\Response;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @final
 */
class ResponseCreatorTestToolTest extends TestCase
{
    public function testWritesJsonBody(): void
    {
        $response = ResponseCreatorTestTool::createJsonResponse(new Response(), ['foo' => 'bar'], 200);

        Assert::assertSame('{"foo":"bar"}', (string)$response->getBody());
        Assert::assertSame('application/json', $response->getHeaderLine('Content-Type'));
    }


    public function testSetsStatusCode(): void
    {
        $response = ResponseCreatorTestTool::createJsonResponse(new Response(), ['created' => true], 201);

        Assert::assertSame(201, $response->getStatusCode());
        Assert::assertSame('{"created":true}', (string)$response->getBody());
    }
}
