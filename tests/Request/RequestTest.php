<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Request;

use BrandEmbassy\Slim\Request\QueryParamMissingException;
use BrandEmbassy\Slim\Request\Request;
use BrandEmbassy\Slim\Request\RequestFieldMissingException;
use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\Response;
use BrandEmbassy\Slim\SlimApplicationFactory;
use BrandEmbassyTest\Slim\Sample\CreateChannelUserRoute;
use BrandEmbassyTest\Slim\SlimAppTester;
use DateTime;
use LogicException;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Slim\Http\Body;
use Slim\Http\Headers;
use Slim\Http\Uri;
use function assert;
use function fopen;
use function is_resource;
use function sprintf;
use function urlencode;

/**
 * @final
 */
class RequestTest extends TestCase
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


    public function testGetParsedBodyAsArray(): void
    {
        $request = $this->createSampleRequest();

        $expectedArray = [
            'thisIsNull' => null,
            'thisIsGandalf' => 'gandalf',
        ];

        Assert::assertSame($expectedArray, $request->getParsedBodyAsArray());
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

        Assert::assertSame(self::DATE_TIME_STRING, $dateTime->format(DateTime::ATOM));
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
        $response = new Response();
        $response = $response->withHeader('hasBeenCalled', 'true');

        /** @var Response $response */
        $responseFromRoute = ($request->getRoute()->getCallable())($request, $response);

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


    public function testResolvingFields(): void
    {
        $request = $this->getDispatchedRequest();

        Assert::assertSame('value', $request->getField('level-1.level-2'));
        Assert::assertNull($request->getField('level-1.level-2-null'));
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
            'level-1' => [
                'level-2' => 'value',
                'level-2-null' => null,
            ],
        ];
    }


    /**
     * @dataProvider getDataForInvalidDateTimeArgument
     *
     * @param mixed[] $arguments
     */
    public function testGettingDateTimeQueryParamThrowsExceptionIfInvalidArgument(
        string $logicExceptionMessage,
        array $arguments
    ): void {
        $slimRequest = $this->createMockRequest($arguments);
        $request = $slimRequest;
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage($logicExceptionMessage);

        $request->getDateTimeQueryParam(self::PARAM_NAME);
    }


    /**
     * @return mixed[]
     */
    public function getDataForInvalidDateTimeArgument(): array
    {
        return [
            'Missing from' => [
                sprintf('Could not find %s in request\'s params', self::PARAM_NAME),
                [],
            ],
            'Invalid from' => [
                sprintf('Could not parse %s as datetime', self::PARAM_NAME),
                [self::PARAM_NAME => '123456789'],
            ],
        ];
    }


    /**
     * @param mixed[] $arguments
     *
     * @return Request&MockInterface
     */
    private function createMockRequest(array $arguments): MockInterface
    {
        /** @var MockInterface&Request $mock */
        $mock = Mockery::mock(Request::class)->makePartial();
        $mock->shouldReceive('getQueryParams')->andReturn($arguments);

        return $mock;
    }


    private function createRequest(StreamInterface $body): Request
    {
        $url = new Uri('https', 'example.com');
        $slimRequest = new Request('POST', $url, new Headers(), [], [], $body);

        return $slimRequest->withHeader('content-type', 'application/json');
    }


    private function createSampleRequest(): Request
    {
        $resource = fopen('php://temp', 'rb+');
        assert(is_resource($resource));
        $body = new Body($resource);
        $body->write('{"thisIsNull": null, "thisIsGandalf": "gandalf"}');
        $body->rewind();

        return $this->createRequest($body);
    }
}
