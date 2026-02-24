<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Response;

use BrandEmbassy\Slim\Response\Response;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Response as Psr7Response;

/**
 * @final
 */
class ResponseTest extends TestCase
{
    public function testGetInnerResponse(): void
    {
        $psrResponse = new Psr7Response();
        $response = new Response($psrResponse);

        Assert::assertSame($psrResponse, $response->getInnerResponse());
    }
}
