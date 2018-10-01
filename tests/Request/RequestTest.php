<?php

namespace BrandEmbassyTest\Slim\Request;

use BrandEmbassy\Slim\MissingApiArgumentException;
use BrandEmbassy\Slim\Request\Request;
use DateTime;
use DateTimeImmutable;
use LogicException;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Slim\Http\Body;
use Slim\Http\Headers;
use Slim\Http\Request as SlimRequest;
use Slim\Http\Uri;

final class RequestTest extends TestCase
{

    private const PARAM_NAME = 'dateFrom';
    private const DATE_TIME_STRING = '2017-06-10T01:00:00+01:00';

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

        self::assertInstanceOf(DateTimeImmutable::class, $dateTime);
        self::assertSame(self::DATE_TIME_STRING, $dateTime->format(DateTime::ATOM));
    }

    /**
     * @dataProvider getDataForInvalidDateTimeArgument
     *
     * @param string $logicExceptionMessage
     * @param array $arguments
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
     * @return array
     */
    public function getDataForInvalidDateTimeArgument(): array
    {
        return [
            'Missing from' => [
                sprintf('Could not find %s in request\'s params', self::PARAM_NAME),
                []
            ],
            'Invalid from' => [
                sprintf('Could not parse %s as datetime', self::PARAM_NAME),
                [self::PARAM_NAME => 123456789]
            ],
        ];
    }

    /**
     * @param array $arguments
     * @return MockInterface&SlimRequest
     */
    private function createMockSlimRequest(array $arguments): MockInterface
    {
        /** @var MockInterface&SlimRequest $mock */
        $mock = Mockery::mock(SlimRequest::class);
        $mock->shouldReceive('getQueryParams')->andReturn($arguments);

        return $mock;
    }

    /**
     * @param StreamInterface $body
     * @return Request
     */
    private function createRequest(StreamInterface $body): Request
    {
        $url = new Uri('https', 'example.com');
        $slimRequest = new SlimRequest('POST', $url, new Headers(), [], [], $body);

        return new Request($slimRequest);
    }

    /**
     * @return Request
     */
    private function createDummyRequest(): Request
    {
        $resource = fopen('php://temp', 'rb+');
        \assert(\is_resource($resource));
        $body = new Body($resource);
        $body->write('{"thisIsNull": null, "thisIsGandalf": "gandalf"}');
        $body->rewind();

        return $this->createRequest($body);
    }

}
