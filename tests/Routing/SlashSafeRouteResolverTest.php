<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Routing;

use BrandEmbassy\Slim\Routing\SlashSafeRouteResolver;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\CallableResolver;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Routing\RouteCollector;
use Slim\Routing\RouteResolver;
use Slim\Routing\RoutingResults;

/**
 * @final
 */
class SlashSafeRouteResolverTest extends TestCase
{
    /**
     * @dataProvider provideMatchingUriCases
     */
    public function testRouteIsMatchedAndParameterPreserved(string $uri, string $expectedRawParamValue): void
    {
        $resolver = $this->createResolverWithRoute('GET', '/items/{id}');

        $result = $resolver->computeRoutingResults($uri, 'GET');

        Assert::assertSame(RoutingResults::FOUND, $result->getRouteStatus());
        Assert::assertSame($expectedRawParamValue, $result->getRouteArguments(false)['id']);
    }


    /**
     * @return array<string, array{string, string}>
     */
    public static function provideMatchingUriCases(): array
    {
        return [
            'plain parameter' => [
                '/items/simple',
                'simple',
            ],
            'encoded slash %2F preserved' => [
                '/items/abc%2Fdef',
                'abc%2Fdef',
            ],
            'encoded colon %3A preserved' => [
                '/items/urn%3Ambid%3Avalue',
                'urn%3Ambid%3Avalue',
            ],
            'encoded plus %2B preserved' => [
                '/items/abc%2Bdef',
                'abc%2Bdef',
            ],
            'encoded space %20 preserved' => [
                '/items/hello%20world',
                'hello%20world',
            ],
            'encoded equals %3D preserved' => [
                '/items/base64value%3D',
                'base64value%3D',
            ],
            'complex URN with multiple encoded chars preserved' => [
                '/items/urn%3Ambid%3AAQAAY4PwoYv7H0b8%2B6Zx7WkS%2BjV%2FzkJMyh9xoms0%3D',
                'urn%3Ambid%3AAQAAY4PwoYv7H0b8%2B6Zx7WkS%2BjV%2FzkJMyh9xoms0%3D',
            ],
            'multiple encoded slashes preserved' => [
                '/items/a%2Fb%2Fc',
                'a%2Fb%2Fc',
            ],
        ];
    }


    public function testEmptyUriGetsLeadingSlash(): void
    {
        $resolver = $this->createResolverWithRoute('GET', '/');

        $result = $resolver->computeRoutingResults('', 'GET');

        Assert::assertSame(RoutingResults::FOUND, $result->getRouteStatus());
    }


    public function testRouteWithEncodedSlashReturnsNotFoundWithDefaultResolver(): void
    {
        $responseFactory = new ResponseFactory();
        $callableResolver = new CallableResolver();
        $routeCollector = new RouteCollector($responseFactory, $callableResolver);

        $routeCollector->map(['GET'], '/items/{id}', $this->createDummyHandler());

        $defaultResult = (new RouteResolver($routeCollector))
            ->computeRoutingResults('/items/abc%2Fdef', 'GET');

        Assert::assertSame(RoutingResults::NOT_FOUND, $defaultResult->getRouteStatus());

        $safeResult = (new SlashSafeRouteResolver($routeCollector))
            ->computeRoutingResults('/items/abc%2Fdef', 'GET');

        Assert::assertSame(RoutingResults::FOUND, $safeResult->getRouteStatus());
    }


    private function createResolverWithRoute(string $method, string $pattern): SlashSafeRouteResolver
    {
        $responseFactory = new ResponseFactory();
        $callableResolver = new CallableResolver();
        $routeCollector = new RouteCollector($responseFactory, $callableResolver);

        $routeCollector->map([$method], $pattern, $this->createDummyHandler());

        return new SlashSafeRouteResolver($routeCollector);
    }


    /**
     * @return callable(ServerRequestInterface, ResponseInterface): ResponseInterface
     */
    private function createDummyHandler(): callable
    {
        return static fn(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface => $response;
    }
}
