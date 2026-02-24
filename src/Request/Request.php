<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Request;

use Adbar\Dot;
use DateTime;
use DateTimeImmutable;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Headers;
use Slim\Psr7\Request as SlimRequest;
use Slim\Routing\Route;
use Slim\Routing\RouteContext;
use Slim\Routing\RoutingResults;
use function array_key_exists;
use function assert;
use function is_array;
use function is_string;
use function sprintf;
use function str_contains;

/**
 * @final
 *
 * Extends Slim 4's PSR-7 Request with convenience methods for common
 * request operations. All PSR-7 methods are inherited from the parent.
 */
class Request extends SlimRequest implements RequestInterface
{
    /**
     * @var Dot<string, mixed[]>|null
     */
    protected ?Dot $dotAnnotatedRequestBody = null;


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


    public function __clone()
    {
        parent::__clone();
        $this->dotAnnotatedRequestBody = null;
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
     * @return mixed
     *
     * @throws RequestFieldMissingException
     */
    public function getField(string $fieldName)
    {
        if ($this->hasField($fieldName)) {
            return $this->getDotAnnotatedRequestBody()->get($fieldName);
        }

        throw RequestFieldMissingException::create($fieldName);
    }


    /**
     * @param mixed $default
     *
     * @return mixed
     */
    public function findField(string $fieldName, $default = null)
    {
        return $this->getDotAnnotatedRequestBody()->get($fieldName, $default);
    }


    public function hasField(string $fieldName): bool
    {
        return $this->getDotAnnotatedRequestBody()->has($fieldName);
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
     * @return string|string[]|null
     */
    public function findQueryParam(string $key, ?string $default = null)
    {
        return $this->getQueryParam($key) ?? $default;
    }


    /**
     * @return string|string[]
     *
     * @throws QueryParamMissingException
     */
    public function getQueryParamStrict(string $key)
    {
        $value = $this->findQueryParam($key);

        if ($value !== null) {
            return $value;
        }

        throw QueryParamMissingException::create($key);
    }


    public function findQueryParamAsString(string $key, ?string $default = null): ?string
    {
        $queryParam = $this->getQueryParam($key);
        assert(!is_array($queryParam));

        return $queryParam ?? $default;
    }


    /**
     * @throws QueryParamMissingException
     */
    public function getQueryParamAsString(string $key): string
    {
        $value = $this->findQueryParamAsString($key);

        if ($value !== null) {
            return $value;
        }

        throw QueryParamMissingException::create($key);
    }


    public function hasQueryParam(string $key): bool
    {
        return array_key_exists($key, $this->getQueryParams());
    }


    /**
     * @throws QueryParamMissingException
     * @throws InvalidArgumentException
     */
    public function getDateTimeQueryParam(string $field, string $format = DateTime::ATOM): DateTimeImmutable
    {
        $datetimeParam = $this->getQueryParamStrict($field);
        assert(is_string($datetimeParam));

        $dateTime = DateTimeImmutable::createFromFormat($format, $datetimeParam);

        if ($dateTime === false) {
            throw new InvalidArgumentException(sprintf('Field %s is not in %s format', $field, $format));
        }

        return $dateTime;
    }


    public function isHtml(): bool
    {
        $acceptHeader = $this->getHeaderLine('accept');

        return str_contains($acceptHeader, 'html');
    }


    public function hasAttribute(string $name): bool
    {
        return array_key_exists($name, $this->getAttributes());
    }


    /**
     * @param mixed $default
     *
     * @return mixed
     */
    public function findAttribute(string $name, $default = null)
    {
        return $this->getAttribute($name, $default);
    }


    /**
     * @return mixed
     *
     * @throws RequestAttributeMissingException
     */
    public function getAttributeStrict(string $name)
    {
        if ($this->hasAttribute($name)) {
            return $this->getAttribute($name);
        }

        throw RequestAttributeMissingException::create($name);
    }


    /**
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function getServerParam(string $key, $default = null)
    {
        $serverParams = $this->getServerParams();

        return $serverParams[$key] ?? $default;
    }


    /**
     * @return Dot<string, mixed[]>
     */
    private function getDotAnnotatedRequestBody(): Dot
    {
        if ($this->dotAnnotatedRequestBody === null) {
            $this->dotAnnotatedRequestBody = new Dot($this->getParsedBodyAsArray());
        }

        return $this->dotAnnotatedRequestBody;
    }
}
