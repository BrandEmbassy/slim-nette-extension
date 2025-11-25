<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Route;

use function apcu_exists;
use function apcu_fetch;
use function apcu_store;
use function in_array;
use function sprintf;
use function strpos;
use function strtok;

/**
 * @final
 */
class OnlyNecessaryRoutesProvider
{
    /**
     * @param mixed[] $allRoutes
     * @param string[] $apiNamesAlwaysInclude
     *
     * @return mixed[]
     */
    public function getRoutes(
        ?string $url,
        array $allRoutes,
        bool $useApcuCache,
        array $apiNamesAlwaysInclude = []
    ): array {
        if ($url === null) {
            return $allRoutes;
        }

        $url = (string)strtok($url, '?');
        $filteredRoutes = [];

        foreach ($allRoutes as $apiName => $apiRoutes) {
            $apiNameIsCurrentFromRequest = strpos($url, '/' . $apiName . '/') !== false;

            if (!$apiNameIsCurrentFromRequest && !in_array($apiName, $apiNamesAlwaysInclude, true)) {
                continue;
            }

            $cacheKey = sprintf('filtered_routes.%s', $apiName);
            if ($apiNameIsCurrentFromRequest && $useApcuCache && apcu_exists($cacheKey)) {
                return apcu_fetch($cacheKey);
            }

            foreach ($apiRoutes as $routeName => $routeDefinitions) {
                foreach ($routeDefinitions as $routeHttpMethod => $routeDefinition) {
                    $filteredRoutes[$apiName][$routeName][$routeHttpMethod] = $routeDefinition;
                }
            }

            if ($apiNameIsCurrentFromRequest && $useApcuCache) {
                apcu_store($cacheKey, $filteredRoutes);
            }
        }

        return $filteredRoutes;
    }
}
