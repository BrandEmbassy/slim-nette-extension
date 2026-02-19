<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Request;

use Adbar\Dot;
use DateTime;
use DateTimeImmutable;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
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
 * Wrapper around PSR-7 ServerRequestInterface providing convenience methods
 * for common request operations. Use getInnerRequest() for direct PSR-7 access.
 */
class Request implements RequestInterface
{
    /**
     * @var Dot<string, mixed[]>|null
     */
    protected ?Dot $dotAnnotatedRequestBody = null;

    private ServerRequestInterface $request;


    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }


    /**
     * Get the inner PSR-7 request
     */
    public function getInnerRequest(): ServerRequestInterface
    {
        return $this->request;
    }


    /**
     * Get routing results from Slim 4's RouteContext
     */
    public function getRoutingResults(): RoutingResults
    {
        return RouteContext::fromRequest($this->request)->getRoutingResults();
    }


    /**
     * Get the matched route.
     */
    public function getRoute(): ?Route
    {
        $routeContext = RouteContext::fromRequest($this->request);
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
        return (array)$this->request->getParsedBody();
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
     * @return string[]|string[][]
     */
    public function getQueryParams(): array
    {
        return $this->request->getQueryParams();
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
        $acceptHeader = $this->request->getHeaderLine('accept');

        return str_contains($acceptHeader, 'html');
    }


    public function hasAttribute(string $name): bool
    {
        return array_key_exists($name, $this->request->getAttributes());
    }


    /**
     * @param mixed $default
     *
     * @return mixed
     */
    public function findAttribute(string $name, $default = null)
    {
        return $this->request->getAttribute($name, $default);
    }


    /**
     * @return mixed
     *
     * @throws RequestAttributeMissingException
     */
    public function getAttributeStrict(string $name)
    {
        if ($this->hasAttribute($name)) {
            return $this->request->getAttribute($name);
        }

        throw RequestAttributeMissingException::create($name);
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


    /**
     * @deprecated use getAttributeStrict or findAttribute
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        return $this->request->getAttribute($name, $default);
    }


    /**
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function getServerParam(string $key, $default = null)
    {
        $serverParams = $this->request->getServerParams();

        return $serverParams[$key] ?? $default;
    }
}
