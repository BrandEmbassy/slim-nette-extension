<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim;

use BrandEmbassy\MockeryTools\Http\ResponseAssertions;
use BrandEmbassyTest\Slim\Sample\BeforeRequestMiddleware;
use BrandEmbassyTest\Slim\Sample\BeforeRouteMiddleware;
use BrandEmbassyTest\Slim\Sample\GoldenKeyAuthMiddleware;
use BrandEmbassyTest\Slim\Sample\GroupMiddleware;
use BrandEmbassyTest\Slim\Sample\InvokeCounterMiddleware;
use BrandEmbassyTest\Slim\Sample\OnlyApiGroupMiddleware;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

final class SlimApplicationFactoryTest extends TestCase
{
    public function testShouldPassSettingsToSlimContainer(): void
    {
        $app = SlimAppTester::createSlimApp();
        $settings = $app->getContainer()->get('settings');

        Assert::assertSame('Sample', $settings['myCustomOption']);
    }


    /**
     * @dataProvider routeResponseDataProvider
     *
     * @param mixed[] $expectedResponseBody
     * @param mixed[] $expectedResponseHeaders
     * @param array<string, string> $headers
     */
    public function testRouteIsDispatchedAndProcessed(
        array $expectedResponseBody,
        array $expectedResponseHeaders,
        int $expectedStatusCode,
        string $httpMethod,
        string $requestUri,
        array $headers = []
    ): void {
        $this->prepareEnvironment($httpMethod, $requestUri, $headers);
        $response = SlimAppTester::runSlimApp();

        ResponseAssertions::assertJsonResponseEqualsArray($expectedResponseBody, $response, $expectedStatusCode);
        ResponseAssertions::assertResponseHeaders($expectedResponseHeaders, $response);
    }


    /**
     * @return mixed[][]
     */
    public function routeResponseDataProvider(): array
    {
        return [
            '200 Hello world as class name' => [
                'expectedResponse' => ['Hello World'],
                'expectedResponseHeaders' => [
                    BeforeRequestMiddleware::HEADER_NAME => 'invoked-0',
                    BeforeRouteMiddleware::HEADER_NAME => 'invoked-1',
                    GroupMiddleware::HEADER_NAME => 'invoked-2',
                ],
                'expectedStatusCode' => 200,
                'httpMethod' => 'GET',
                'requestUri' => '/tests/app/hello-world-as-class-name',
            ],
            '200 Hello world as service name' => [
                'expectedResponse' => ['Hello World'],
                'expectedResponseHeaders' => [
                    BeforeRequestMiddleware::HEADER_NAME => 'invoked-0',
                    BeforeRouteMiddleware::HEADER_NAME => 'invoked-1',
                    GroupMiddleware::HEADER_NAME => 'invoked-2',
                ],
                'expectedStatusCode' => 200,
                'httpMethod' => 'GET',
                'requestUri' => '/tests/app/hello-world-as-service-name',
            ],
            '404 Not found' => [
                'expectedResponse' => ['error' => 'Sample NotFoundHandler here!'],
                'expectedResponseHeaders' => [BeforeRequestMiddleware::HEADER_NAME => 'invoked-0'],
                'expectedStatusCode' => 404,
                'httpMethod' => 'POST',
                'requestUri' => '/tests/non-existing/path',
            ],
            '405 Not allowed' => [
                'expectedResponse' => ['error' => 'Sample NotAllowedHandler here!'],
                'expectedResponseHeaders' => [BeforeRequestMiddleware::HEADER_NAME => 'invoked-0'],
                'expectedStatusCode' => 405,
                'httpMethod' => 'PATCH',
                'requestUri' => '/tests/api/channels',
            ],
            '500 is 500' => [
                'expectedResponse' => ['error' => 'Error or not to error, that\'s the question!'],
                'expectedResponseHeaders' => [],
                'expectedStatusCode' => 500,
                'httpMethod' => 'POST',
                'requestUri' => '/tests/api/error',
            ],
            '401 Unauthorized' => [
                'expectedResponse' => ['error' => 'YOU SHALL NOT PASS!'],
                'expectedResponseHeaders' => [BeforeRequestMiddleware::HEADER_NAME => 'invoked-0'],
                'expectedStatusCode' => 401,
                'httpMethod' => 'POST',
                'requestUri' => '/tests/api/channels',
            ],
            'Token authorization passed' => [
                'expectedResponse' => ['status' => 'created'],
                'expectedResponseHeaders' => [
                    BeforeRequestMiddleware::HEADER_NAME => 'invoked-0',
                    BeforeRouteMiddleware::HEADER_NAME => 'invoked-1',
                    OnlyApiGroupMiddleware::HEADER_NAME => 'invoked-2',
                    GroupMiddleware::HEADER_NAME => 'invoked-3',
                ],
                'expectedStatusCode' => 201,
                'httpMethod' => 'POST',
                'requestUri' => '/tests/api/channels',
                'headers' => ['HTTP_X_API_KEY' => GoldenKeyAuthMiddleware::ACCESS_TOKEN],
            ],
            'Controller get users' => [
                'expectedResponse' => ['users' => []],
                'expectedResponseHeaders' => [BeforeRequestMiddleware::HEADER_NAME => 'invoked-0'],
                'expectedStatusCode' => 200,
                'httpMethod' => 'GET',
                'requestUri' => '/tests/app/users',
            ],
            'Controller create user' => [
                'expectedResponse' => ['status' => 'created'],
                'expectedResponseHeaders' => [BeforeRequestMiddleware::HEADER_NAME => 'invoked-0'],
                'expectedStatusCode' => 201,
                'httpMethod' => 'POST',
                'requestUri' => '/tests/app/users',
            ],
        ];
    }


    public function testMiddlewareInvokeOrder(): void
    {
        $expectedHeaders = [
            InvokeCounterMiddleware::getName('A') => 'invoked-0',
            InvokeCounterMiddleware::getName('B') => 'invoked-1',
            InvokeCounterMiddleware::getName('C') => 'invoked-2',
            InvokeCounterMiddleware::getName('D') => 'invoked-3',
            InvokeCounterMiddleware::getName('E') => 'invoked-4',
            InvokeCounterMiddleware::getName('F') => 'invoked-5',
            InvokeCounterMiddleware::getName('G') => 'invoked-6',
            InvokeCounterMiddleware::getName('H') => 'invoked-7',
            InvokeCounterMiddleware::getName('I') => 'invoked-8',
            InvokeCounterMiddleware::getName('J') => 'invoked-9',
            InvokeCounterMiddleware::getName('K') => 'invoked-10',
            InvokeCounterMiddleware::getName('L') => 'invoked-11',
        ];

        $this->prepareEnvironment('POST', '/api/test');
        $response = SlimAppTester::runSlimApp(__DIR__ . '/no-prefix-config.neon');

        ResponseAssertions::assertResponseHeaders($expectedHeaders, $response);
    }


    public function testRootRouteIsDispatched(): void
    {
        $this->prepareEnvironment('GET', '/');
        $response = SlimAppTester::runSlimApp(__DIR__ . '/no-prefix-config.neon');

        ResponseAssertions::assertResponseStatusCode(200, $response);
    }


    /**
     * @param array<string> $headers
     */
    private function prepareEnvironment(string $requestMethod, string $requestUrlPath, array $headers = []): void
    {
        MiddlewareInvocationCounter::reset();

        $_SERVER['HTTP_HOST'] = 'api.brandembassy.com';
        $_SERVER['REQUEST_URI'] = $requestUrlPath;
        $_SERVER['REQUEST_METHOD'] = $requestMethod;

        foreach ($headers as $name => $value) {
            $_SERVER[$name] = $value;
        }
    }
}
