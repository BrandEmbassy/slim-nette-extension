<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Middleware;

use BrandEmbassy\Slim\Middleware\MiddlewareFactory;
use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;
use Nette\DI\Container;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Slim\Psr7\Factory\ServerRequestFactory;
use stdClass;

/**
 * @final
 */
class MiddlewareFactoryTest extends TestCase
{
    public function testMiddlewareIsResolvedLazilyNotDuringCreation(): void
    {
        $counter = new class {
            public int $value = 0;
        };

        $container = $this->createMock(Container::class);
        $container->method('getByName')
            ->with('lazyMiddleware')
            ->willReturnCallback(static function () use ($counter): callable {
                $counter->value++;

                return static fn(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface => $response;
            });

        $factory = new MiddlewareFactory($container);

        $middleware = $factory->createFromIdentifier('lazyMiddleware');

        Assert::assertSame(0, $counter->value, 'Service must NOT be resolved during createFromIdentifier()');

        $request = (new ServerRequestFactory())->createServerRequest('GET', '/test');
        $handler = $this->createMock(RequestHandlerInterface::class);

        $middleware->process($request, $handler);

        Assert::assertSame(1, $counter->value, 'Service must be resolved when middleware processes a request');
    }


    public function testMiddlewareIsResolvedOnEachRequest(): void
    {
        $counter = new class {
            public int $value = 0;
        };

        $container = $this->createMock(Container::class);
        $container->method('getByName')
            ->willReturnCallback(static function () use ($counter): callable {
                $counter->value++;

                return static fn(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface => $response;
            });

        $factory = new MiddlewareFactory($container);
        $middleware = $factory->createFromIdentifier('someMiddleware');

        $request = (new ServerRequestFactory())->createServerRequest('GET', '/test');
        $handler = $this->createMock(RequestHandlerInterface::class);

        $middleware->process($request, $handler);
        $middleware->process($request, $handler);

        Assert::assertSame(2, $counter->value, 'Service must be resolved on each request');
    }


    public function testThrowsExceptionWhenResolvedServiceIsNotCallable(): void
    {
        $container = $this->createMock(Container::class);
        $container->method('getByName')
            ->willReturn(new stdClass());

        $factory = new MiddlewareFactory($container);
        $middleware = $factory->createFromIdentifier('notCallableMiddleware');

        $request = (new ServerRequestFactory())->createServerRequest('GET', '/test');
        $handler = $this->createMock(RequestHandlerInterface::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Resolved middleware "notCallableMiddleware" is not callable.');

        $middleware->process($request, $handler);
    }


    public function testCreateFromIdentifiersReturnsCorrectCount(): void
    {
        $container = $this->createMock(Container::class);

        $factory = new MiddlewareFactory($container);
        $middlewares = $factory->createFromIdentifiers(['a', 'b', 'c']);

        Assert::assertCount(3, $middlewares);
    }
}
