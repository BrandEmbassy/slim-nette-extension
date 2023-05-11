<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Route;

use function apcu_exists;
use function apcu_fetch;
use function apcu_store;
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
     *
     * @return mixed[]
     */
    public function getRoutes(?string $url, array $allRoutes, bool $useApcuCache): array
    {
        if ($url === null) {
            return $allRoutes;
        }

        $url = (string)strtok($url, '?');
        $filteredRoutes = [];

        foreach ($allRoutes as $apiName => $apiRoutes) {
            if (strpos($url, '/' . $apiName) === false) {
                continue;
            }

            $cacheKey = sprintf('filtered_routes.%s', $apiName);
            if ($useApcuCache && apcu_exists($cacheKey)) {
                return apcu_fetch($cacheKey);
            }

            foreach ($apiRoutes as $routeName => $routeDefinitions) {
                foreach ($routeDefinitions as $routeHttpMethod => $routeDefinition) {
                    $filteredRoutes[$apiName][$routeName][$routeHttpMethod] = $routeDefinition;
                }
            }

            if ($useApcuCache) {
                apcu_store($cacheKey, $filteredRoutes);
            }
        }

        return $filteredRoutes;
    }
}
