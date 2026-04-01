<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Routing;

use Slim\Routing\Dispatcher;
use Slim\Routing\RouteResolver;
use Slim\Routing\RoutingResults;

/**
 * @final
 *
 * Custom RouteResolver that skips rawurldecode() on the URI before dispatching.
 *
 * Slim 4's default RouteResolver calls rawurldecode() on the URI before dispatching
 * to FastRoute. This decodes percent-encoded characters (%2F, %3A, %2B, etc.) into
 * their literal forms, which breaks route matching for parameters containing encoded
 * special characters (e.g. base64-encoded URN identifiers).
 *
 * This resolver matches Slim 3's behavior: the URI is passed to FastRoute as-is,
 * preserving all percent-encoded characters in route parameters.
 */
class SlashSafeRouteResolver extends RouteResolver
{
    public function computeRoutingResults(string $uri, string $method): RoutingResults
    {
        if ($uri === '' || $uri[0] !== '/') {
            $uri = '/' . $uri;
        }

        return (new Dispatcher($this->routeCollector))->dispatch($method, $uri);
    }
}
