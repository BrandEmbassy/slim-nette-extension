<?php

namespace BrandEmbassyTest\Slim;

use BrandEmbassy\Slim\Response\ResponseInterface;
use BrandEmbassy\Slim\SlimApplicationFactory;
use BrandEmbassy\Slim\Request\Request;
use BrandEmbassy\Slim\Response\Response;
use LogicException;
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

class SlimApplicationFactoryTest extends PHPUnit_Framework_TestCase
{

    public function testShouldBeHandledByNotFoundErrorHandlerRouting()
    {
        $request = $this->createRequest('POST', '/non-existing/path');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Dummy NotFoundHandler here!');

        $this->createSlimApp()->process($request, new Response(new \Slim\Http\Response()));
    }

    public function testShouldBeHandledNotAllowedHandlerRouting()
    {
        $request = $this->createRequest('PATCH', '/new-api/2.0/channels');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Dummy API NotAllowedHandler here!');

        $this->createSlimApp()->process($request, new Response(new \Slim\Http\Response()));
    }

    public function testShouldDenyAccessByMiddleware()
    {
        $request = $this->createRequest('POST', '/new-api/2.0/channels');

        $response = $this->createSlimApp()->process($request, new Response(new \Slim\Http\Response()));

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals('{"error":"YOU SHALL NOT PASS!"}', $this->getContents($response));
    }

    public function testShouldAllowAccessByMiddleware()
    {
        $request = $this->createRequest(
            'POST',
            '/new-api/2.0/channels',
            ['goldenKey' => 'uber-secret-token-made-of-pure-gold']
        );

        $response = $this->createSlimApp()->process($request, new Response(new \Slim\Http\Response()));

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('{"channelId":"fb_1234"}', $this->getContents($response));
    }

    /**
     * @return Container
     */
    private function createContainer()
    {
        $loader = new ContainerLoader(__DIR__ . '/temp');
        $class = $loader->load(
            function ($compiler) {
                /** @var Compiler $compiler */
                $compiler->loadConfig(__DIR__ . '/config.neon');
                $compiler->addExtension('extensions', new ExtensionsExtension());
            }
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
     * @return App
     */
    private function createSlimApp()
    {
        /** @var SlimApplicationFactory $factory */
        $factory = $this->createContainer()->getByType(SlimApplicationFactory::class);

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
