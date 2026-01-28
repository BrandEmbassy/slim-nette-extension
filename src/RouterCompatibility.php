<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim;

use Slim\Interfaces\RouteCollectorInterface;
use Slim\Interfaces\RouteInterface;
use Slim\Interfaces\RouteParserInterface;

/**
 * @final
 *
 * Router compatibility wrapper for backward compatibility with Slim 3
 */
class RouterCompatibility
{
    private RouteCollectorInterface $routeCollector;

    private RouteParserInterface $routeParser;


    public function __construct(RouteCollectorInterface $routeCollector, RouteParserInterface $routeParser)
    {
        $this->routeCollector = $routeCollector;
        $this->routeParser = $routeParser;
    }


    /**
     * @return RouteInterface[]
     */
    public function getRoutes(): array
    {
        return $this->routeCollector->getRoutes();
    }


    /**
     * Build the path for a named route including the base path
     *
     * @param string $name        Route name
     * @param array  $data        Named argument replacement data
     * @param array  $queryParams Optional query string parameters
     */
    public function urlFor(string $name, array $data = [], array $queryParams = []): string
    {
        return $this->routeParser->urlFor($name, $data, $queryParams);
    }
}
