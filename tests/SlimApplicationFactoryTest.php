<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim;

use BrandEmbassy\Slim\Request\Request;
use BrandEmbassy\Slim\Response\Response;
use BrandEmbassy\Slim\Response\ResponseInterface;
use BrandEmbassy\Slim\SlimApplicationFactory;
use BrandEmbassyTest\Slim\Sample\GoldenKeyAuthMiddleware;
use BrandEmbassyTest\Slim\Tools\ResponseAssertions;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nette\DI\Extensions\ExtensionsExtension;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Slim\App;
use Slim\Http\Body;
use Slim\Http\Headers;
use Slim\Http\Request as SlimRequest;
use Slim\Http\Uri;
use function assert;
use function fopen;
use function is_resource;
use function md5;

final class SlimApplicationFactoryTest extends TestCase
{
    public function testShouldPassSettingsToSlimContainer(): void
    {
        $app = $this->createSlimApp();
        $settings = $app->getContainer()->get('settings');

        Assert::assertSame('Sample', $settings['myCustomOption']);
    }


    public function testShouldAllowEmptyErrorHandlers(): void
    {
        $this->createSlimApp(__DIR__ . '/configNoHandlers.neon');
        $this->expectNotToPerformAssertions();
    }


    /**
     * @dataProvider routeResponseDataProvider
     *
     * @param mixed[] $expectedResponseBody
     */
    public function testRouteIsDispatchedAndProcessed(
        array $expectedResponseBody,
        int $expectedStatusCode,
        Request $request
    ): void {
        $response = $this->createSlimApp()->process($request, new Response(new \Slim\Http\Response()));

        ResponseAssertions::assertJsonResponseEqualsArray($expectedResponseBody, $response, $expectedStatusCode);
    }


    /**
     * @return mixed[][]
     */
    public static function routeResponseDataProvider(): array
    {
        return [
            '200 Hello world' => [
                'expectedResponseBody' => ['Hello World'],
                'expectedStatusCode' => 200,
                'request' => self::createRequest('GET', '/app'),
            ],
            '404 Not found' => [
                'expectedResponseBody' => ['error' => 'Sample NotFoundHandler here!'],
                'expectedStatusCode' => 404,
                'request' => self::createRequest('POST', '/non-existing/path'),
            ],
            '405 Not allowed' => [
                'expectedResponseBody' => ['error' => 'Sample NotAllowedHandler here!'],
                'expectedStatusCode' => 405,
                'request' => self::createRequest('PATCH', '/new-api/2.0/channels'),
            ],
            '500 is 500' => [
                'expectedResponseBody' => ['error' => "Error or not to error, that's the question!"],
                'expectedStatusCode' => 500,
                'request' => self::createRequest('POST', '/new-api/2.0/error'),
            ],
            '401 Unauthorized' => [
                'expectedResponseBody' => ['error' => 'YOU SHALL NOT PASS!'],
                'expectedStatusCode' => 401,
                'request' => self::createRequest('POST', '/new-api/2.0/channels'),
            ],
            'Token authorization passed' => [
                'expectedResponseBody' => ['status' => 'created'],
                'expectedStatusCode' => 201,
                'request' => self::createRequest(
                    'POST',
                    '/new-api/2.0/channels',
                    ['goldenKey' => GoldenKeyAuthMiddleware::ACCESS_TOKEN]
                ),
            ],
        ];
    }


    public function testShouldProcessBothGlobalMiddlewares(): void
    {
        $request = self::createRequest('POST', '/new-api/2.0/channels');

        /** @var ResponseInterface $response */
        $response = $this->createSlimApp()->process($request, new Response(new \Slim\Http\Response()));

        Assert::assertSame(
            ['proof-for-before-request'],
            $response->getHeader('processed-by-before-request-middleware')
        );

        Assert::assertSame(
            ['proof-for-before-route'],
            $response->getHeader('processed-by-before-route-middlewares')
        );
    }


    public function testShouldProcessBeforeRequestMiddleware(): void
    {
        $request = self::createRequest('POST', '/non-existing/path');

        /** @var ResponseInterface $response */
        $response = $this->createSlimApp()->process($request, new Response(new \Slim\Http\Response()));

        Assert::assertSame(
            ['proof-for-before-request'],
            $response->getHeader('processed-by-before-request-middleware')
        );
    }


    private function createContainer(string $configPath = __DIR__ . '/config.neon'): Container
    {
        $loader = new ContainerLoader(__DIR__ . '/../temp', true);
        $class = $loader->load(
            static function (Compiler $compiler) use ($configPath): void {
                $compiler->loadConfig($configPath);
                $compiler->addExtension('extensions', new ExtensionsExtension());
            },
            md5($configPath)
        );

        /** @var Container $container */
        $container = new $class();

        return $container;
    }


    /**
     * @param array<string> $headers
     */
    private static function createRequest(string $requestMethod, string $requestUrlPath, array $headers = []): Request
    {
        $body = fopen('php://temp', 'rb+');
        assert(is_resource($body));
        $slimRequest = new SlimRequest(
            $requestMethod,
            new Uri('http', 'api.be.com', 80, $requestUrlPath),
            new Headers($headers),
            [],
            [],
            new Body($body)
        );

        return new Request($slimRequest);
    }


    private function createSlimApp(string $configPath = __DIR__ . '/config.neon'): App
    {
        /** @var SlimApplicationFactory $factory */
        $factory = $this->createContainer($configPath)->getByType(SlimApplicationFactory::class);

        return $factory->create();
    }
}
