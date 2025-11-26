<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Tools;

use Nette\Utils\Json;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;

/**
 * @final
 */
class ResponseAssertions
{
    private const STATUS_CODE_200 = 200;


    /**
     * @param mixed[] $expectedArray
     */
    public static function assertJsonResponseEqualsArray(
        array $expectedArray,
        ResponseInterface $response,
        int $expectedStatusCode = self::STATUS_CODE_200
    ): void {
        $expectedJson = Json::encode($expectedArray);

        Assert::assertJsonStringEqualsJsonString($expectedJson, self::parseAsString($response));
        self::assertResponseStatusCode($expectedStatusCode, $response);
    }


    /**
     * @param array<string, string> $expectedHeaders
     */
    public static function assertResponseHeaders(array $expectedHeaders, ResponseInterface $response): void
    {
        foreach ($expectedHeaders as $headerName => $headerValue) {
            self::assertResponseHeader($headerValue, $headerName, $response);
        }
    }


    public static function assertResponseHeader(
        string $expectedHeaderValue,
        string $headerName,
        ResponseInterface $response
    ): void {
        Assert::assertSame($expectedHeaderValue, $response->getHeaderLine($headerName));
    }


    public static function assertResponseStatusCode(int $expectedStatusCode, ResponseInterface $response): void
    {
        Assert::assertSame($expectedStatusCode, $response->getStatusCode());
    }


    public static function parseAsString(ResponseInterface $response): string
    {
        return (string)$response->getBody();
    }
}
