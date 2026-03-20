<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use function array_map;
use function array_merge;

/**
 * @final
 */
class MiddlewareGroups
{
    /**
     * @var array<string, MiddlewareInterface[]>
     */
    private array $groups;


    /**
     * @param array<string, string[]> $middlewareGroups
     */
    public function __construct(array $middlewareGroups, MiddlewareFactory $middlewareFactory)
    {
        $this->groups = array_map(
            static fn(array $middlewares): array => $middlewareFactory->createFromIdentifiers($middlewares),
            $middlewareGroups,
        );
    }


    /**
     * @return MiddlewareInterface[]
     */
    public function getMiddlewares(string $groupName): array
    {
        return $this->groups[$groupName] ?? [];
    }


    /**
     * @param string[] $groupNames
     *
     * @return MiddlewareInterface[]
     */
    public function getMiddlewaresForMultipleGroups(array $groupNames): array
    {
        if ($groupNames === []) {
            return [];
        }

        $groupsToMerge = array_map(
            fn(string $groupName): array => $this->getMiddlewares($groupName),
            $groupNames,
        );

        return array_merge(...$groupsToMerge);
    }
}
