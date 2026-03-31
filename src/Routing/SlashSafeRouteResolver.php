<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Routing;

use Slim\Interfaces\DispatcherInterface;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Interfaces\RouteInterface;
use Slim\Interfaces\RouteResolverInterface;
use Slim\Routing\Dispatcher;
use Slim\Routing\RoutingResults;
use function rawurldecode;
use function str_replace;

/**
 * @final
 *
 * Custom RouteResolver that preserves %2F (encoded forward slashes) in URI paths.
 *
 * Slim 4's default RouteResolver calls rawurldecode() on the URI before dispatching
 * to FastRoute. This decodes %2F into literal /, which breaks route matching for
 * parameters containing encoded slashes (e.g. base64-encoded identifiers).
 *
 * This resolver temporarily replaces %2F with a placeholder before decoding,
 * then restores it, so FastRoute sees %2F as part of the segment — not as
 * a path separator.
 */
class SlashSafeRouteResolver implements RouteResolverInterface
{
    private const ENCODED_SLASH = '%2F';
    private const ENCODED_SLASH_UPPER = '%2f';
    private const PLACEHOLDER = '{{ENCODED_SLASH}}';

    private RouteCollectorInterface $routeCollector;

    private DispatcherInterface $dispatcher;


    public function __construct(
        RouteCollectorInterface $routeCollector,
        ?DispatcherInterface $dispatcher = null,
    ) {
        $this->routeCollector = $routeCollector;
        $this->dispatcher = $dispatcher ?? new Dispatcher($routeCollector);
    }


    public function computeRoutingResults(string $uri, string $method): RoutingResults
    {
        // Protect encoded slashes from rawurldecode
        $uri = str_replace(
            [self::ENCODED_SLASH, self::ENCODED_SLASH_UPPER],
            [self::PLACEHOLDER, self::PLACEHOLDER],
            $uri,
        );

        $uri = rawurldecode($uri);

        // Restore encoded slashes
        $uri = str_replace(self::PLACEHOLDER, self::ENCODED_SLASH, $uri);

        if ($uri === '' || $uri[0] !== '/') {
            $uri = '/' . $uri;
        }

        return $this->dispatcher->dispatch($method, $uri);
    }


    public function resolveRoute(string $identifier): RouteInterface
    {
        return $this->routeCollector->lookupRoute($identifier);
    }
}
