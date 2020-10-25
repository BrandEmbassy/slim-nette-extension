<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Middleware;

use function array_map;
use function array_merge_recursive;

final class MiddlewareGroups
{
    /**
     * @var array<string, callable[]>
     */
    private $groups;


    /**
     * @param array<string, string[]> $middlewareGroups
     */
    public function __construct(array $middlewareGroups, MiddlewareFactory $middlewareFactory)
    {
        $this->groups = array_map(
            static function (array $middlewares) use ($middlewareFactory): array {
                return $middlewareFactory->createFromIdentifiers($middlewares);
            },
            $middlewareGroups
        );
    }


    /**
     * @return callable[]
     */
    public function getMiddlewares(string $groupName): array
    {
        return $this->groups[$groupName] ?? [];
    }


    /**
     * @param string[] $groupNames
     *
     * @return callable[]
     */
    public function getMiddlewaresForMultipleGroups(array $groupNames): array
    {
        if ($groupNames === []) {
            return [];
        }

        $groupsToMerge = array_map(
            function (string $groupName): array {
                return $this->getMiddlewares($groupName);
            },
            $groupNames
        );

        return array_merge_recursive(...$groupsToMerge);
    }
}
