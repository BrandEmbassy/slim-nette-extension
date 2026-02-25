<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Request;

use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\Response;
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


    public function testQueryParamResolving(): void
    {
        $request = $this->getDispatchedRequest('?foo=bar&two=2&null=null&array[]=item1&array[]=item2');

        Assert::assertSame('bar', $request->getQueryParam('foo'));
        Assert::assertSame('2', $request->getQueryParam('two'));
        Assert::assertSame('null', $request->getQueryParam('null'));
        Assert::assertSame(['item1', 'item2'], $request->getQueryParam('array'));
        Assert::assertSame('default', $request->getQueryParam('non-existing', 'default'));
        Assert::assertNull($request->getQueryParam('non-existing'));
    }


    public function testGetRoute(): void
    {
        $request = $this->getDispatchedRequest('?foo=bar&two=2&null=null&array[]=item1&array[]=item2');
        $response = new Response();
        $response = $response->withHeader('hasBeenCalled', 'true');

        // In Slim 4, route callables receive (request, response, args)
        $route = $request->getRoute();
        assert($route !== null, 'Route should be set after dispatching');

        $callable = $route->getCallable();
        assert(is_callable($callable), 'Route callable must be callable');

        $responseFromRoute = $callable(
            $request->getInnerRequest(),
            $response,
            $request->getRouteArguments()
        );

        Assert::assertSame(['true'], $responseFromRoute->getHeader('hasBeenCalled'));
    }


    public function testRestResolvingAttributes(): void
    {
        $request = $this->getDispatchedRequest();

        Assert::assertSame('123', $request->getRouteArgument('channelId'));
        Assert::assertTrue($request->hasRouteArgument('channelId'));
        Assert::assertFalse($request->hasRouteArgument('non-existing'));
        Assert::assertSame(['channelId' => '123'], $request->getRouteArguments());
        Assert::assertSame('123', $request->findRouteArgument('channelId'));
        Assert::assertSame('default', $request->findRouteArgument('non-existing', 'default'));
    }


    private function getDispatchedRequest(string $queryString = ''): RequestInterface
    {
        $this->prepareEnvironment($queryString);

        $container = SlimAppTester::createContainer();
        $container->getByType(SlimApplicationFactory::class)->create()->runAndReturnResponse();

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
