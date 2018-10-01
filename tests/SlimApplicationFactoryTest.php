<?php

namespace BrandEmbassyTest\Slim;

use BrandEmbassy\Slim\Response\ResponseInterface;
use BrandEmbassy\Slim\SlimApp;
use BrandEmbassy\Slim\SlimApplicationFactory;
use BrandEmbassy\Slim\Request\Request;
use BrandEmbassy\Slim\Response\Response;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nette\DI\Extensions\ExtensionsExtension;
use PHPUnit_Framework_TestCase;
use Slim\App;
use Slim\Http\Body;
use Slim\Http\Headers;
use Slim\Http\Request as SlimRequest;
use Slim\Http\Uri;

final class SlimApplicationFactoryTest extends PHPUnit_Framework_TestCase
{

    public function testShouldAllowEmptyErrorHandlers()
    {
        $result = $this->createSlimApp(__DIR__ . '/configNoHandlers.neon');
        $this->assertInstanceOf(SlimApp::class, $result);
    }

    public function testShouldBeHandledByNotFoundErrorHandler()
    {
        $request = $this->createRequest('POST', '/non-existing/path');

        /** @var ResponseInterface $response */
        $response = $this->createSlimApp()->process($request, new Response(new \Slim\Http\Response()));

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('{"error":"Dummy NotFoundHandler here!"}', $this->getContents($response));
    }

    public function testShouldBeHandledByNotAllowedHandler()
    {
        $request = $this->createRequest('PATCH', '/new-api/2.0/channels');

        /** @var ResponseInterface $response */
        $response = $this->createSlimApp()->process($request, new Response(new \Slim\Http\Response()));

        $this->assertEquals(405, $response->getStatusCode());
        $this->assertEquals('{"error":"Dummy NotAllowedHandler here!"}', $this->getContents($response));
    }

    public function testShouldBeHandledByApiErrorHandler()
    {
        $request = $this->createRequest('POST', '/new-api/2.0/error');

        /** @var ResponseInterface $response */
        $response = $this->createSlimApp()->process($request, new Response(new \Slim\Http\Response()));

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('{"error":"Error or not to error, that\'s the question!"}', $this->getContents($response));
    }

    public function testShouldDenyRequestByAccessMiddleware()
    {
        $request = $this->createRequest('POST', '/new-api/2.0/channels');

        /** @var ResponseInterface $response */
        $response = $this->createSlimApp()->process($request, new Response(new \Slim\Http\Response()));

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals('{"error":"YOU SHALL NOT PASS!"}', $this->getContents($response));
    }

    public function testShouldAllowRequestByAccessMiddleware()
    {
        $request = $this->createRequest(
            'POST',
            '/new-api/2.0/channels',
            ['goldenKey' => 'uber-secret-token-made-of-pure-gold']
        );

        /** @var ResponseInterface $response */
        $response = $this->createSlimApp()->process($request, new Response(new \Slim\Http\Response()));

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('{"channelId":"fb_1234"}', $this->getContents($response));
    }

    public function testShouldProcessBothGlobalMiddlewares()
    {
        $request = $this->createRequest('POST', '/new-api/2.0/channels');

        /** @var ResponseInterface $response */
        $response = $this->createSlimApp()->process($request, new Response(new \Slim\Http\Response()));

        $this->assertEquals(
            ['proof-for-before-request'],
            $response->getHeader('processed-by-before-request-middleware')
        );

        $this->assertEquals(
            ['proof-for-before-route'],
            $response->getHeader('processed-by-before-route-middlewares')
        );
    }

    public function testShouldProcessBeforeRequestMiddleware()
    {
        $request = $this->createRequest('POST', '/non-existing/path');

        /** @var ResponseInterface $response */
        $response = $this->createSlimApp()->process($request, new Response(new \Slim\Http\Response()));

        $this->assertEquals(
            ['proof-for-before-request'],
            $response->getHeader('processed-by-before-request-middleware')
        );
    }

    /**
     * @param string $configPath
     * @return Container
     */
    private function createContainer($configPath = __DIR__ . '/config.neon')
    {
        $loader = new ContainerLoader(__DIR__ . '/temp', true);
        $class = $loader->load(
            function ($compiler) use ($configPath) {
                /** @var Compiler $compiler */
                $compiler->loadConfig($configPath);
                $compiler->addExtension('extensions', new ExtensionsExtension());
            },
            \md5($configPath)
        );

        return new $class;
    }

    /**
     * @param string $requestMethod
     * @param string $requestUrlPath
     * @param string[] $headers
     * @return Request
     */
    private function createRequest($requestMethod, $requestUrlPath, array $headers = [])
    {
        return new Request(
            new SlimRequest(
                $requestMethod,
                new Uri('http', 'api.be.com', 80, $requestUrlPath),
                new Headers($headers),
                [],
                [],
                new Body(fopen('php://temp', 'rb+'))
            )
        );
    }

    /**
     * @param string $configPath
     * @return App
     */
    private function createSlimApp($configPath = __DIR__ . '/config.neon')
    {
        /** @var SlimApplicationFactory $factory */
        $factory = $this->createContainer($configPath)->getByType(SlimApplicationFactory::class);

        return $factory->create();
    }

    /**
     * @param ResponseInterface $response
     * @return string
     */
    private function getContents(ResponseInterface $response)
    {
        $body = $response->getBody();
        $body->rewind();

        return $body->getContents();
    }

}
