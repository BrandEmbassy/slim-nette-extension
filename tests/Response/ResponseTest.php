<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Response;

use BrandEmbassy\Slim\Response\DefaultResponseFactory;
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
        $responseFactory = new DefaultResponseFactory();
        $response = $responseFactory->create();
        $response = $response->withJson($parsedBody);

        Assert::assertSame($parsedBody, $response->getParsedBodyAsArray());
    }
}
