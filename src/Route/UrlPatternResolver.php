<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Route;

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


    public function resolve(string $routeDefinitionPattern): string
    {
        return $this->apiPrefix . $routeDefinitionPattern;
    }
}
