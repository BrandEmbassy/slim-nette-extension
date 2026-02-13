<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Response;

use BrandEmbassy\Slim\Response\Response;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Slim\Http\StatusCode;

/**
 * @final
 */
class ResponseTest extends TestCase
{
    public function testGetParsedBodyAsArray(): void
    {
        $parsedBody = ['foo' => 'bar'];
        $response = new Response();
        $response = $response->withJson($parsedBody);

        Assert::assertSame($parsedBody, $response->getParsedBodyAsArray());
    }


    public function testLegacyConstructorWithDefaults(): void
    {
        $response = new Response();

        Assert::assertSame(StatusCode::HTTP_OK, $response->getStatusCode());
    }


    public function testLegacyConstructorWithStatus(): void
    {
        $response = new Response(StatusCode::HTTP_NOT_FOUND);

        Assert::assertSame(StatusCode::HTTP_NOT_FOUND, $response->getStatusCode());
    }


    public function testConstructorWithPsrResponse(): void
    {
        $psrResponse = new GuzzleResponse(
            StatusCode::HTTP_CREATED,
            ['Content-Type' => 'application/json', 'X-Custom' => 'test-value'],
            '{"key":"value"}',
        );

        $response = new Response($psrResponse);

        Assert::assertSame(StatusCode::HTTP_CREATED, $response->getStatusCode());
        Assert::assertSame('application/json', $response->getHeaderLine('Content-Type'));
        Assert::assertSame('test-value', $response->getHeaderLine('X-Custom'));
        Assert::assertSame('{"key":"value"}', (string)$response->getBody());
    }


    public function testGetInnerResponseReturnsSelf(): void
    {
        $response = new Response();

        Assert::assertSame($response, $response->getInnerResponse());
    }


    public function testConstructorWithPsrResponsePreservesBodyForParsing(): void
    {
        $parsedBody = ['foo' => 'bar'];
        $psrResponse = new GuzzleResponse(
            StatusCode::HTTP_OK,
            ['Content-Type' => 'application/json'],
            '{"foo":"bar"}',
        );

        $response = new Response($psrResponse);

        Assert::assertSame($parsedBody, $response->getParsedBodyAsArray());
    }
}
