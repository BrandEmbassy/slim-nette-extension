<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Request;

use Slim\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use BrandEmbassy\Slim\Request\Request;
use BrandEmbassy\Slim\SlimApplicationFactory;
use BrandEmbassyTest\Slim\Sample\CreateChannelUserRoute;
use BrandEmbassyTest\Slim\SlimAppTester;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @final
 */
class RequestTest extends TestCase
{
    private const CHANNEL_ID = '123';


    public function testGetRoute(): void
    {
        $serverRequest = $this->getDispatchedRequest('?foo=bar&two=2&null=null&array[]=item1&array[]=item2');
        $request = new Request($serverRequest);

        $route = $request->getRoute();
        assert($route !== null, 'Route should be set after dispatching');

        $callable = $route->getCallable();
        assert(is_callable($callable), 'Route callable must be callable');

        $responseFromRoute = $callable(
            $serverRequest,
            new Response(),
            $request->getRouteArguments()
        );

        Assert::assertSame(200, $responseFromRoute->getStatusCode());
    }


    public function testRestResolvingAttributes(): void
    {
        $serverRequest = $this->getDispatchedRequest();
        $request = new Request($serverRequest);

        Assert::assertSame('123', $request->getRouteArgument('channelId'));
        Assert::assertTrue($request->hasRouteArgument('channelId'));
        Assert::assertFalse($request->hasRouteArgument('non-existing'));
        Assert::assertSame(['channelId' => '123'], $request->getRouteArguments());
        Assert::assertSame('123', $request->findRouteArgument('channelId'));
        Assert::assertSame('default', $request->findRouteArgument('non-existing', 'default'));
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
