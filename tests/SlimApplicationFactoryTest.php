<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim;

use BrandEmbassy\MockeryTools\Http\ResponseAssertions;
use BrandEmbassyTest\Slim\Sample\GoldenKeyAuthMiddleware;
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


    public function testShouldAllowEmptyErrorHandlers(): void
    {
        SlimAppTester::runSlimApp(__DIR__ . '/configNoHandlers.neon');
        $this->expectNotToPerformAssertions();
    }


    /**
     * @dataProvider routeResponseDataProvider
     *
     * @param mixed[] $expectedResponseBody
     * @param array<string, string> $headers
     */
    public function testRouteIsDispatchedAndProcessed(
        array $expectedResponseBody,
        int $expectedStatusCode,
        string $httpMethod,
        string $requestUri,
        array $headers = []
    ): void {
        $this->prepareEnvironment($httpMethod, $requestUri, $headers);
        $response = SlimAppTester::runSlimApp();

        ResponseAssertions::assertJsonResponseEqualsArray($expectedResponseBody, $response, $expectedStatusCode);
    }


    /**
     * @return mixed[][]
     */
    public function routeResponseDataProvider(): array
    {
        return [
            '200 Hello world as class name' => [
                'expectedResponse' => ['Hello World'],
                'expectedStatusCode' => 200,
                'httpMethod' => 'GET',
                'requestUri' => '/app/hello-world-as-class-name',
            ],
            '200 Hello world as service name' => [
                'expectedResponse' => ['Hello World'],
                'expectedStatusCode' => 200,
                'httpMethod' => 'GET',
                'requestUri' => '/app/hello-world-as-service-name',
            ],
            '404 Not found' => [
                'expectedResponse' => ['error' => 'Sample NotFoundHandler here!'],
                'expectedStatusCode' => 404,
                'httpMethod' => 'POST',
                'requestUri' => '/non-existing/path',
            ],
            '405 Not allowed' => [
                'expectedResponse' => ['error' => 'Sample NotAllowedHandler here!'],
                'expectedStatusCode' => 405,
                'httpMethod' => 'PATCH',
                'requestUri' => '/api/channels',
            ],
            '500 is 500' => [
                'expectedResponse' => ['error' => 'Error or not to error, that\'s the question!'],
                'expectedStatusCode' => 500,
                'httpMethod' => 'POST',
                'requestUri' => '/api/error',
            ],
            '401 Unauthorized' => [
                'expectedResponse' => ['error' => 'YOU SHALL NOT PASS!'],
                'expectedStatusCode' => 401,
                'httpMethod' => 'POST',
                'requestUri' => '/api/channels',
            ],
            'Token authorization passed' => [
                'expectedResponse' => ['status' => 'created'],
                'expectedStatusCode' => 201,
                'httpMethod' => 'POST',
                'requestUri' => '/api/channels',
                'headers' => ['HTTP_X_API_KEY' => GoldenKeyAuthMiddleware::ACCESS_TOKEN],
            ],
            'Controller get users' => [
                'expectedResponse' => ['users' => []],
                'expectedStatusCode' => 200,
                'httpMethod' => 'GET',
                'requestUri' => '/app/users',
            ],
            'Controller create user' => [
                'expectedResponse' => ['status' => 'created'],
                'expectedStatusCode' => 201,
                'httpMethod' => 'POST',
                'requestUri' => '/app/users',
            ],
        ];
    }


    public function testShouldProcessBothGlobalMiddlewares(): void
    {
        $this->prepareEnvironment('POST', '/api/channels');

        $response = SlimAppTester::runSlimApp();

        ResponseAssertions::assertResponseHeaders(
            [
                'processed-by-before-request-middleware' => 'proof-for-before-request',
                'processed-by-before-route-middleware' => 'proof-for-before-route',
            ],
            $response
        );
    }


    public function testShouldProcessBeforeRequestMiddleware(): void
    {
        $this->prepareEnvironment('POST', '/non-existing/path');

        $response = SlimAppTester::runSlimApp();

        ResponseAssertions::assertResponseHeader(
            'proof-for-before-request',
            'processed-by-before-request-middleware',
            $response
        );
    }


    /**
     * @param array<string> $headers
     */
    private function prepareEnvironment(string $requestMethod, string $requestUrlPath, array $headers = []): void
    {
        $_SERVER['HTTP_HOST'] = 'api.brandembassy.com';
        $_SERVER['REQUEST_URI'] = $requestUrlPath;
        $_SERVER['REQUEST_METHOD'] = $requestMethod;

        foreach ($headers as $name => $value) {
            $_SERVER[$name] = $value;
        }
    }
}
