<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Routing;

use BrandEmbassy\Slim\Routing\SlashSafeRouteResolver;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Slim\Interfaces\RouteCollectorInterface;

/**
 * @final
 */
class SlashSafeRouteResolverTest extends TestCase
{
    /**
     * @dataProvider provideUriCases
     */
    public function testComputeRoutingResultsPreservesEncodedSlashes(
        string $inputUri,
        string $expectedDispatchedUri,
    ): void {
        $dispatcher = new CapturingDispatcher();
        $routeCollector = $this->createMock(RouteCollectorInterface::class);
        $resolver = new SlashSafeRouteResolver($routeCollector, $dispatcher);

        $resolver->computeRoutingResults($inputUri, 'GET');

        Assert::assertSame($expectedDispatchedUri, $dispatcher->getLastDispatchedUri());
    }


    /**
     * @return array<string, array{string, string}>
     */
    public static function provideUriCases(): array
    {
        return [
            'encoded slash %2F preserved' => [
                '/channels/123/threads/abc%2Fdef/sender-actions',
                '/channels/123/threads/abc%2Fdef/sender-actions',
            ],
            'encoded slash %2f lowercase preserved' => [
                '/channels/123/threads/abc%2fdef/sender-actions',
                '/channels/123/threads/abc%2Fdef/sender-actions',
            ],
            'other percent-encoded chars decoded normally' => [
                '/channels/123/threads/hello%20world/actions',
                '/channels/123/threads/hello world/actions',
            ],
            'mixed encoded slash and other encoding' => [
                '/channels/123/threads/abc%2Fdef%20ghi/actions',
                '/channels/123/threads/abc%2Fdef ghi/actions',
            ],
            'no encoding passes through' => [
                '/channels/123/threads/plain/actions',
                '/channels/123/threads/plain/actions',
            ],
            'empty uri gets leading slash' => [
                '',
                '/',
            ],
            'multiple encoded slashes preserved' => [
                '/path/a%2Fb%2Fc',
                '/path/a%2Fb%2Fc',
            ],
        ];
    }
}
