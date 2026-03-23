<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Request;

use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Headers;
use Slim\Psr7\Request as SlimRequest;
use Slim\Routing\Route;
use Slim\Routing\RouteContext;
use Slim\Routing\RoutingResults;

/**
 * @final
 *
 * Extends Slim 4's PSR-7 Request with convenience methods for common
 * request operations. All PSR-7 methods are inherited from the parent.
 */
class Request extends SlimRequest implements RequestInterface
{
    public function __construct(ServerRequestInterface $request)
    {
        parent::__construct(
            $request->getMethod(),
            $request->getUri(),
            new Headers($request->getHeaders()),
            $request->getCookieParams(),
            $request->getServerParams(),
            $request->getBody(),
            $request->getUploadedFiles(),
        );

        $parsedBody = $request->getParsedBody();

        if ($parsedBody !== null) {
            $this->parsedBody = $parsedBody;
        }

        foreach ($request->getAttributes() as $name => $value) {
            $this->attributes[$name] = $value;
        }

        // Bridge Slim 4 route to legacy 'route' attribute for backward compatibility.
        // Slim 3 stored the route in 'route', Slim 4 stores it in '__route__'.
        // Always override 'route' because test requests may have a pre-set
        // 'route' attribute with empty arguments.
        $slimRoute = $request->getAttribute('__route__');

        if ($slimRoute instanceof Route) {
            $this->attributes['route'] = $slimRoute;
        }

        $queryParams = $request->getQueryParams();

        if ($queryParams !== []) {
            $this->queryParams = $queryParams;
        }
    }


    /**
     * Get routing results from Slim 4's RouteContext
     */
    public function getRoutingResults(): RoutingResults
    {
        return RouteContext::fromRequest($this)->getRoutingResults();
    }


    /**
     * Get the matched route.
     */
    public function getRoute(): ?Route
    {
        $routeContext = RouteContext::fromRequest($this);
        $route = $routeContext->getRoute();

        // PHPStan: RouteContext::getRoute() returns RouteInterface|null but we need Route|null
        // At runtime, this will always be Route|null in Slim 4
        return $route instanceof Route ? $route : null;
    }


    /**
     * @return array<string, string>
     */
    public function getRouteArguments(): array
    {
        $routingResults = $this->getRoutingResults();

        return $routingResults->getRouteArguments();
    }


    public function hasRouteArgument(string $argument): bool
    {
        return isset($this->getRouteArguments()[$argument]);
    }


    /**
     * @throws RouteArgumentMissingException
     */
    public function getRouteArgument(string $argument): string
    {
        if ($this->hasRouteArgument($argument)) {
            return $this->getRouteArguments()[$argument];
        }

        throw RouteArgumentMissingException::create($argument);
    }


    public function findRouteArgument(string $argument, ?string $default = null): ?string
    {
        return $this->getRouteArguments()[$argument] ?? $default;
    }


    /**
     * @return mixed[]
     */
    public function getParsedBodyAsArray(): array
    {
        return (array)$this->getParsedBody();
    }


    /**
     * @param mixed|null $default
     *
     * @return string|string[]|null
     */
    public function getQueryParam(string $key, $default = null)
    {
        $params = $this->getQueryParams();

        return $params[$key] ?? $default;
    }


    /**
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function getServerParam(string $key, $default = null)
    {
        return $this->getServerParams()[$key] ?? $default;
    }
}
