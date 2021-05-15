<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Route;

use function trim;

final class UrlPatternResolver
{
    /**
     * @var string
     */
    private $apiPrefix;


    public function __construct(string $apiPrefix)
    {
        $this->apiPrefix = $apiPrefix;
    }


    public function resolve(string $apiNamespace, string $routePattern): string
    {
        $routePath = $this->resolveRoutePath($apiNamespace, $routePattern);

        $pattern = $this->apiPrefix . $routePath;

        if ($pattern === '') {
            return '/';
        }

        return $pattern;
    }


    public function resolveRoutePath(string $apiNamespace, string $routePattern): string
    {
        $apiNamespace = trim($apiNamespace, '/');
        $routePattern = trim($routePattern, '/');

        if ($apiNamespace !== '') {
            $apiNamespace = '/' . $apiNamespace;
        }

        if ($routePattern !== '') {
            $routePattern = '/' . $routePattern;
        }

        return $apiNamespace . $routePattern;
    }
}
