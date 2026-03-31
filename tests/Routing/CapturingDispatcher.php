<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Routing;

use Slim\Interfaces\DispatcherInterface;
use Slim\Routing\RoutingResults;

/**
 * @final
 *
 * Test double that captures the URI dispatched to it.
 */
class CapturingDispatcher implements DispatcherInterface
{
    private ?string $lastDispatchedUri = null;


    public function getLastDispatchedUri(): ?string
    {
        return $this->lastDispatchedUri;
    }


    public function dispatch(string $method, string $uri): RoutingResults
    {
        $this->lastDispatchedUri = $uri;

        return new RoutingResults($this, $method, $uri, RoutingResults::NOT_FOUND);
    }


    /**
     * @return string[]
     */
    public function getAllowedMethods(string $uri): array
    {
        return [];
    }
}
