<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Response;

use BrandEmbassy\Slim\Response\JsonResponse;
use BrandEmbassy\Slim\Response\Response;
use Nette\Utils\Json;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @final
 */
class ResponseTest extends TestCase
{
    public function testResponseImplementsInterface(): void
    {
        $response = new Response();

        Assert::assertInstanceOf(\BrandEmbassy\Slim\Response\ResponseInterface::class, $response);
        Assert::assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $response);
    }


    public function testJsonResponse(): void
    {
        $parsedBody = ['foo' => 'bar'];
        $response = new Response();
        $response = JsonResponse::from($response, $parsedBody);

        $decoded = Json::decode((string)$response->getBody(), Json::FORCE_ARRAY);
        Assert::assertSame($parsedBody, $decoded);
    }
}
