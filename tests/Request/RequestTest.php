<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Request;

use BrandEmbassy\MockeryTools\DateTime\DateTimeAssertions;
use BrandEmbassy\Slim\Request\QueryParamMissingException;
use BrandEmbassy\Slim\Request\RequestFieldMissingException;
use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\SlimApplicationFactory;
use BrandEmbassyTest\Slim\Sample\CreateChannelUserRoute;
use BrandEmbassyTest\Slim\SlimAppTester;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use function urlencode;

final class RequestTest extends TestCase
{
    private const PARAM_NAME = 'dateFrom';
    private const DATE_TIME_STRING = '2017-06-10T01:00:00+01:00';
    private const CHANNEL_ID = '123';


    public function testShouldDistinguishBetweenNullAndEmptyOption(): void
    {
        $request = $this->getDispatchedRequest();

        Assert::assertTrue($request->hasField('thisIsNull'));
        Assert::assertFalse($request->hasField('nonExistingField'));
        Assert::assertTrue($request->hasField('thisIsGandalf'));
    }


    public function testShouldRaiseExceptionForMissingRequiredField(): void
    {
        $request = $this->getDispatchedRequest();

        Assert::assertSame('gandalf', $request->getField('thisIsGandalf'));
        $this->expectException(RequestFieldMissingException::class);
        $this->expectExceptionMessage('Field "nonExistingField" is missing in request body');
        $request->getField('nonExistingField');
    }


    public function testGettingDateTimeQueryParam(): void
    {
        $request = $this->getDispatchedRequest('?' . self::PARAM_NAME . '=' . urlencode(self::DATE_TIME_STRING));

        $dateTime = $request->getDateTimeQueryParam(self::PARAM_NAME);
        DateTimeAssertions::assertDateTimeAtomEqualsDateTime(self::DATE_TIME_STRING, $dateTime);
    }


    public function testQueryParamResolving(): void
    {
        $request = $this->getDispatchedRequest('?foo=bar&two=2&null=null&array[]=item1&array[]=item2');

        Assert::assertTrue($request->hasQueryParam('null'));
        Assert::assertFalse($request->hasQueryParam('non-existing'));
        Assert::assertSame('bar', $request->getQueryParamStrict('foo'));
        Assert::assertSame('bar', $request->getQueryParamAsString('foo'));
        Assert::assertSame('bar', $request->findQueryParamAsString('foo'));
        Assert::assertSame('2', $request->getQueryParamStrict('two'));
        Assert::assertSame('null', $request->getQueryParamStrict('null'));
        Assert::assertSame(['item1', 'item2'], $request->getQueryParamStrict('array'));
        Assert::assertSame('default', $request->findQueryParam('non-existing', 'default'));
        Assert::assertNull($request->findQueryParam('non-existing'));
        Assert::assertNull($request->findQueryParamAsString('non-existing'));

        $this->expectException(QueryParamMissingException::class);
        $this->expectExceptionMessage('Query param "non-existing" is missing in request URI');

        $request->getQueryParamStrict('non-existing');
    }


    public function testGetRoute(): void
    {
        $request = $this->getDispatchedRequest('?foo=bar&two=2&null=null&array[]=item1&array[]=item2');

        Assert::assertInstanceOf(CreateChannelUserRoute::class, $request->getRoute()->getCallable());
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


    public function testResolvingFields(): void
    {
        $request = $this->getDispatchedRequest();

        Assert::assertSame('value', $request->getField('level-1.level-2'));
    }


    private function getDispatchedRequest(string $queryString = ''): RequestInterface
    {
        $this->prepareEnvironment($queryString);

        $container = SlimAppTester::createContainer();
        $container->getByType(SlimApplicationFactory::class)->create()->run();

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
            'level-1' => ['level-2' => 'value'],
        ];
    }
}
