<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Response;

use BrandEmbassyTest\Slim\Tools\ResponseCreatorTestTool;
use BrandEmbassy\Slim\Response\Response;
use BrandEmbassy\Slim\Response\ResponseInterface;
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

        Assert::assertInstanceOf(ResponseInterface::class, $response);
        Assert::assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $response);
    }


    public function testJsonResponse(): void
    {
        $parsedBody = ['foo' => 'bar'];
        $response = new Response();
        $response = ResponseCreatorTestTool::createJsonResponse($response, $parsedBody);

        $decoded = Json::decode((string)$response->getBody(), Json::FORCE_ARRAY);
        Assert::assertSame($parsedBody, $decoded);
    }
}
