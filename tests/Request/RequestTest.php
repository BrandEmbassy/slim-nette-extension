<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Request;

use Psr\Http\Message\ServerRequestInterface;
use BrandEmbassy\Slim\SlimApplicationFactory;
use BrandEmbassyTest\Slim\Sample\CreateChannelUserRoute;
use BrandEmbassyTest\Slim\SlimAppTester;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Slim\Routing\RouteContext;

/**
 * @final
 */
class RequestTest extends TestCase
{
    private const CHANNEL_ID = '123';


    public function testRouteArgumentsAreSetAsAttributes(): void
    {
        $request = $this->getDispatchedRequest('?foo=bar&two=2');

        Assert::assertSame('123', $request->getAttribute('channelId'));
    }


    public function testQueryParamsArePreserved(): void
    {
        $request = $this->getDispatchedRequest('?foo=bar&two=2&null=null&array[]=item1&array[]=item2');

        $queryParams = $request->getQueryParams();
        Assert::assertSame('bar', $queryParams['foo']);
        Assert::assertSame('2', $queryParams['two']);
    }


    private function getDispatchedRequest(string $queryString = ''): ServerRequestInterface
    {
        $this->prepareEnvironment($queryString);

        $container = SlimAppTester::createContainer();
        $request = SlimAppTester::createServerRequest();
        $container->getByType(SlimApplicationFactory::class)->create()->handle($request);

        $updateChannelRoute = $container->getByType(CreateChannelUserRoute::class);

        return $updateChannelRoute->getRequest();
    }


    private function prepareEnvironment(string $queryString): void
    {
        $_SERVER['HTTP_HOST'] = 'api.brandembassy.com';
        $_SERVER['HTTP_CONTENT_TYPE'] = 'multipart/form-data';
        $_SERVER['REQUEST_URI'] = '/tests/api/channels/' . self::CHANNEL_ID . '/users' . $queryString;
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $_POST = [
            'thisIsNull' => null,
            'thisIsGandalf' => 'gandalf',
            'level-1' => [
                'level-2' => 'value',
                'level-2-null' => null,
            ],
        ];
    }
}
