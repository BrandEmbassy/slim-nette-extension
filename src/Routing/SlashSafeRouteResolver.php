<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Routing;

use Slim\Routing\Dispatcher;
use Slim\Routing\RouteResolver;
use Slim\Routing\RoutingResults;
use function preg_match;
use function preg_replace_callback;
use function rawurldecode;

/**
 * @final
 *
 * Custom RouteResolver that preserves %2F (encoded forward slashes) in URI paths.
 *
 * Slim 4's default RouteResolver calls rawurldecode() on the URI before dispatching
 * to FastRoute. This decodes %2F into literal /, which breaks route matching for
 * parameters containing encoded slashes (e.g. base64-encoded identifiers).
 *
 * This resolver decodes all percent-encoded triplets except %2F, so FastRoute
 * sees %2F as part of the segment — not as a path separator.
 */
class SlashSafeRouteResolver extends RouteResolver
{
    private const ENCODED_SLASH_PATTERN = '/%2f/i';


    public function computeRoutingResults(string $uri, string $method): RoutingResults
    {
        $uri = $this->rawurldecodeSafe($uri);

        if ($uri === '' || $uri[0] !== '/') {
            $uri = '/' . $uri;
        }

        return (new Dispatcher($this->routeCollector))->dispatch($method, $uri);
    }


    /**
     * Decodes all percent-encoded triplets except %2F (encoded forward slash).
     */
    private function rawurldecodeSafe(string $uri): string
    {
        return (string)preg_replace_callback(
            '/%[0-9A-Fa-f]{2}/',
            static function (array $match): string {
                if (preg_match(self::ENCODED_SLASH_PATTERN, $match[0]) === 1) {
                    return '%2F';
                }

                return rawurldecode($match[0]);
            },
            $uri,
        );
    }
}
