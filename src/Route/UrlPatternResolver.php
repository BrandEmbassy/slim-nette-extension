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


    public function resolve(string $version, string $routeName): string
    {
        $version = trim($version, '/');
        $routeName = trim($routeName, '/');

        if ($version !== '') {
            $version = '/' . $version;
        }

        if ($routeName !== '') {
            $routeName = '/' . $routeName;
        }

        $pattern = $this->apiPrefix . $version . $routeName;

        if ($pattern === '') {
            return '/';
        }

        return $pattern;
    }
}
