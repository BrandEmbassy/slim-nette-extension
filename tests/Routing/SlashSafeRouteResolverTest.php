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
    public function testRouteWithEncodedSlashIsMatched(string $uri, string $expectedRawParamValue): void
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
            'encoded slash %2F preserved in parameter' => [
                '/items/abc%2Fdef',
                'abc%2Fdef',
            ],
            'encoded slash %2f lowercase preserved as-is' => [
                '/items/abc%2fdef',
                'abc%2fdef',
            ],
            'other percent-encoded chars decoded normally' => [
                '/items/hello%20world',
                'hello world',
            ],
            'mixed encoded slash and space' => [
                '/items/abc%2Fdef%20ghi',
                'abc%2Fdef ghi',
            ],
            'multiple encoded slashes preserved' => [
                '/items/a%2Fb%2Fc',
                'a%2Fb%2Fc',
            ],
            'encoded curly braces decoded normally' => [
                '/items/%7B%7Bsome-value%7D%7D',
                '{{some-value}}',
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
