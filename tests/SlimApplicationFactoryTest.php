<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim;

use BrandEmbassy\Slim\Request\Request;
use BrandEmbassy\Slim\Response\Response;
use BrandEmbassy\Slim\Response\ResponseInterface;
use BrandEmbassy\Slim\SlimApplicationFactory;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nette\DI\Extensions\ExtensionsExtension;
use PHPUnit\Framework\TestCase;
use Slim\App;
use Slim\Http\Body;
use Slim\Http\Headers;
use Slim\Http\Request as SlimRequest;
use Slim\Http\Uri;

final class SlimApplicationFactoryTest extends TestCase
{

    public function testShouldAllowEmptyErrorHandlers(): void
    {
        $this->createSlimApp(__DIR__ . '/configNoHandlers.neon');
        $this->expectNotToPerformAssertions();
    }

    public function testShouldBeHandledByNotFoundErrorHandler(): void
    {
        $request = $this->createRequest('POST', '/non-existing/path');

        /** @var ResponseInterface $response */
        $response = $this->createSlimApp()->process($request, new Response(new \Slim\Http\Response()));

        self::assertEquals(404, $response->getStatusCode());
        self::assertEquals('{"error":"Dummy NotFoundHandler here!"}', $this->getContents($response));
    }

    public function testShouldBeHandledByNotAllowedHandler(): void
    {
        $request = $this->createRequest('PATCH', '/new-api/2.0/channels');

        /** @var ResponseInterface $response */
        $response = $this->createSlimApp()->process($request, new Response(new \Slim\Http\Response()));

        self::assertEquals(405, $response->getStatusCode());
        self::assertEquals('{"error":"Dummy NotAllowedHandler here!"}', $this->getContents($response));
    }

    public function testShouldBeHandledByApiErrorHandler(): void
    {
        $request = $this->createRequest('POST', '/new-api/2.0/error');

        /** @var ResponseInterface $response */
        $response = $this->createSlimApp()->process($request, new Response(new \Slim\Http\Response()));

        self::assertEquals(500, $response->getStatusCode());
        self::assertEquals('{"error":"Error or not to error, that\'s the question!"}', $this->getContents($response));
    }

    public function testShouldDenyRequestByAccessMiddleware(): void
    {
        $request = $this->createRequest('POST', '/new-api/2.0/channels');

        /** @var ResponseInterface $response */
        $response = $this->createSlimApp()->process($request, new Response(new \Slim\Http\Response()));

        self::assertEquals(401, $response->getStatusCode());
        self::assertEquals('{"error":"YOU SHALL NOT PASS!"}', $this->getContents($response));
    }

    public function testShouldAllowRequestByAccessMiddleware(): void
    {
        $request = $this->createRequest(
            'POST',
            '/new-api/2.0/channels',
            ['goldenKey' => 'uber-secret-token-made-of-pure-gold']
        );

        /** @var ResponseInterface $response */
        $response = $this->createSlimApp()->process($request, new Response(new \Slim\Http\Response()));

        self::assertEquals(201, $response->getStatusCode());
        self::assertEquals('{"channelId":"fb_1234"}', $this->getContents($response));
    }

    public function testShouldProcessBothGlobalMiddlewares(): void
    {
        $request = $this->createRequest('POST', '/new-api/2.0/channels');

        /** @var ResponseInterface $response */
        $response = $this->createSlimApp()->process($request, new Response(new \Slim\Http\Response()));

        self::assertEquals(
            ['proof-for-before-request'],
            $response->getHeader('processed-by-before-request-middleware')
        );

        self::assertEquals(
            ['proof-for-before-route'],
            $response->getHeader('processed-by-before-route-middlewares')
        );
    }

    public function testShouldProcessBeforeRequestMiddleware(): void
    {
        $request = $this->createRequest('POST', '/non-existing/path');

        /** @var ResponseInterface $response */
        $response = $this->createSlimApp()->process($request, new Response(new \Slim\Http\Response()));

        self::assertEquals(
            ['proof-for-before-request'],
            $response->getHeader('processed-by-before-request-middleware')
        );
    }

    private function createContainer(string $configPath = __DIR__ . '/config.neon'): Container
    {
        $loader = new ContainerLoader(__DIR__ . '/temp', true);
        $class = $loader->load(
            static function (Compiler $compiler) use ($configPath): void {
                $compiler->loadConfig($configPath);
                $compiler->addExtension('extensions', new ExtensionsExtension());
            },
            \md5($configPath)
        );

        return new $class();
    }

    /**
     * @param string $requestMethod
     * @param string $requestUrlPath
     * @param string[] $headers
     * @return Request
     */
    private function createRequest(string $requestMethod, string $requestUrlPath, array $headers = []): Request
    {
        $body = \fopen('php://temp', 'rb+');
        \assert(\is_resource($body));
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

    private function getContents(ResponseInterface $response): string
    {
        $body = $response->getBody();
        $body->rewind();

        return $body->getContents();
    }

}
