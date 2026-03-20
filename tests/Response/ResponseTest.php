<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Response;

use BrandEmbassyTest\Slim\Tools\ResponseCreatorTestTool;
use BrandEmbassy\Slim\Response\Response;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @final
 */
class ResponseTest extends TestCase
{
    public function testGetParsedBodyAsArray(): void
    {
        $parsedBody = ['foo' => 'bar'];
        $response = new Response();
        $response = ResponseCreatorTestTool::createJsonResponse($response, $parsedBody, 200);

        Assert::assertSame($parsedBody, $response->getParsedBodyAsArray());
    }
}
