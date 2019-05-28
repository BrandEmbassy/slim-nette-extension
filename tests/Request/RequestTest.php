<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Request;

use BrandEmbassy\Slim\MissingApiArgumentException;
use BrandEmbassy\Slim\Request\Request;
use DateTime;
use LogicException;
use Mockery;
use Mockery\MockInterface;
use Nette\Utils\Json;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Slim\Http\Body;
use Slim\Http\Headers;
use Slim\Http\Request as SlimRequest;
use Slim\Http\Uri;
use function assert;
use function fopen;
use function is_resource;
use function sprintf;

final class RequestTest extends TestCase
{
    private const PARAM_NAME = 'dateFrom';
    private const DATE_TIME_STRING = '2017-06-10T01:00:00+01:00';
    private const DUMMY_REQUEST_BODY = '{"thisIsNull":null,"thisIsGandalf":"gandalf"}';


    public function testWithParsedBodyWithNotKnownMediaType(): void
    {
        $request = $this->createDummyRequest();
        $request = $request->withHeader('Content-Type', 'application/xml');

        $this->expectExceptionMessage(
            'Parsed body could not be set because there is no encoder for media type \'application/xml\''
        );
        $this->expectException(RuntimeException::class);
        $request->withParsedBody([]);
    }


    public function testWithBody(): void
    {
        $expectedUpdatedBody = '{"thisIsNull":null,"thisIsGandalf":"gandalf","thisIsNewField":"new field"}';
        $expectedUpdatedParsedBody = Json::decode($expectedUpdatedBody, Json::FORCE_ARRAY);

        $request = $this->createDummyRequest();

        $newRequestBody = $this->createRequestBody($expectedUpdatedBody);
        $requestNew = $request->withBody($newRequestBody);

        $this->assertEquals($expectedUpdatedBody, $requestNew->getBody()->getContents());
        $this->assertEquals($expectedUpdatedParsedBody, $requestNew->getParsedBody());
    }


    public function testWithParsedBody(): void
    {
        $expectedUpdatedBody = '{"thisIsNull":null,"thisIsGandalf":"gandalf","thisIsNewField":"new field"}';
        $expectedUpdatedParsedBody = Json::decode($expectedUpdatedBody, Json::FORCE_ARRAY);

        $request = $this->createDummyRequest();

        $body = $request->getParsedBody();
        $body['thisIsNewField'] = 'new field';
        $requestNew = $request->withParsedBody($body);

        $this->assertEquals($expectedUpdatedBody, $requestNew->getBody()->getContents());
        $this->assertEquals($expectedUpdatedParsedBody, $requestNew->getParsedBody());
    }

    public function testShouldDistinguishBetweenNullAndEmptyOption(): void
    {
        $request = $this->createDummyRequest();

        self::assertTrue($request->hasField('thisIsNull'));
        self::assertFalse($request->hasField('nonExistingField'));
        self::assertTrue($request->hasField('thisIsGandalf'));
    }


    public function testShouldRaiseExceptionForMissingRequiredField(): void
    {
        $request = $this->createDummyRequest();

        self::assertEquals('gandalf', $request->getField('thisIsGandalf'));
        $this->expectException(MissingApiArgumentException::class);
        $request->getField('nonExistingField');
    }


    public function testGettingDateTimeQueryParam(): void
    {
        $arguments = [self::PARAM_NAME => self::DATE_TIME_STRING];
        $slimRequest = $this->createMockSlimRequest($arguments);

        $request = new Request($slimRequest);
        $dateTime = $request->getDateTimeQueryParam(self::PARAM_NAME);

        self::assertSame(self::DATE_TIME_STRING, $dateTime->format(DateTime::ATOM));
    }


    /**
     * @dataProvider getDataForInvalidDateTimeArgument
     * @param string  $logicExceptionMessage
     * @param mixed[] $arguments
     */
    public function testGettingDateTimeQueryParamThrowsExceptionIfInvalidArgument(
        string $logicExceptionMessage,
        array $arguments
    ): void {
        $slimRequest = $this->createMockSlimRequest($arguments);
        $request = new Request($slimRequest);
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
     * @return MockInterface&SlimRequest
     */
    private function createMockSlimRequest(array $arguments): MockInterface
    {
        /** @var MockInterface&SlimRequest $mock */
        $mock = Mockery::mock(SlimRequest::class);
        $mock->shouldReceive('getQueryParams')->andReturn($arguments);

        return $mock;
    }


    private function createRequest(StreamInterface $body): Request
    {
        $url = new Uri('https', 'example.com');
        $slimRequest = new SlimRequest('POST', $url, new Headers(), [], [], $body);
        $slimRequest = $slimRequest->withAddedHeader('Content-Type', 'application/json');

        return new Request($slimRequest);
    }


    private function createDummyRequest(): Request
    {
        return $this->createRequest($this->createRequestBody(self::DUMMY_REQUEST_BODY));
    }


    private function createRequestBody(string $bodyAsString): Body
    {
        $resource = fopen('php://temp', 'rb+');
        assert(is_resource($resource));
        $body = new Body($resource);
        $body->write($bodyAsString);
        $body->rewind();

        return $body;
    }

}
