<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Request;

use Adbar\Dot;
use DateTime;
use DateTimeImmutable;
use InvalidArgumentException;
use Slim\Http\Request as SlimRequest;
use Slim\Route;
use function array_key_exists;
use function assert;
use function is_array;
use function is_string;
use function sprintf;
use function str_contains;

/**
 * @method string[]|string[][] getQueryParams()
 * @method string|string[]|null getQueryParam(string $key, ?string $default = null)
 *
 * @final
 */
class Request extends SlimRequest implements RequestInterface
{
    private const ROUTE_INFO_ATTRIBUTE = 'routeInfo';

    private const ROUTE_ATTRIBUTE = 'route';

    /**
     * @var Dot<string, mixed[]>|null
     */
    protected $dotAnnotatedRequestBody;


    /**
     * Private SlimRequest instance when wrapping an existing request
     */
    private ?SlimRequest $wrappedRequest = null;


    /**
     * Constructor that accepts either a SlimRequest to wrap or the standard Slim constructor arguments
     *
     * @param string|SlimRequest $method Either HTTP method string or a SlimRequest instance to wrap
     * @param UriInterface|string $uri
     * @param array<string, string[]> $headers
     * @param array<string, string> $cookies
     * @param array<string, mixed> $serverParams
     * @param StreamInterface|null $body
     */
    public function __construct(
        $method = 'GET',
        $uri = '',
        $headers = [],
        $cookies = [],
        $serverParams = [],
        $body = null
    ) {
        // If first argument is actually a SlimRequest, store it and delegate to it
        if ($method instanceof SlimRequest) {
            $this->wrappedRequest = $method;

            // Call parent constructor with properties from the wrapped request
            // Note: getHeaders() returns array, but parent expects HeadersInterface
            // So we need to reconstruct the Headers object
            $headersArray = $method->getHeaders();
            $headersObject = new \Slim\Http\Headers($headersArray);

            parent::__construct(
                $method->getMethod(),
                $method->getUri(),
                $headersObject,
                $method->getCookieParams(),
                $method->getServerParams(),
                $method->getBody()
            );
        } else {
            // Standard Slim constructor call
            parent::__construct($method, $uri, $headers, $cookies, $serverParams, $body);
        }
    }


    /**
     * Override methods to delegate to wrapped request if available
     */
    public function getAttributes()
    {
        return $this->wrappedRequest !== null
            ? $this->wrappedRequest->getAttributes()
            : parent::getAttributes();
    }


    public function getAttribute($name, $default = null)
    {
        return $this->wrappedRequest !== null
            ? $this->wrappedRequest->getAttribute($name, $default)
            : parent::getAttribute($name, $default);
    }


    public function getParsedBody()
    {
        return $this->wrappedRequest !== null
            ? $this->wrappedRequest->getParsedBody()
            : parent::getParsedBody();
    }


    public function getQueryParams()
    {
        return $this->wrappedRequest !== null
            ? $this->wrappedRequest->getQueryParams()
            : parent::getQueryParams();
    }


    public function __clone()
    {
        parent::__clone();
        $this->dotAnnotatedRequestBody = null;

        // Clone the wrapped request if present
        if ($this->wrappedRequest !== null) {
            $this->wrappedRequest = clone $this->wrappedRequest;
        }
    }


    public function withAttribute($name, $value)
    {
        $clone = parent::withAttribute($name, $value);

        // If we have a wrapped request, update it as well to maintain consistency
        if ($clone->wrappedRequest !== null) {
            $clone->wrappedRequest = $clone->wrappedRequest->withAttribute($name, $value);
        }

        return $clone;
    }


    public function getRoute(): Route
    {
        $route = $this->getAttribute(self::ROUTE_ATTRIBUTE);
        assert($route instanceof Route);

        return $route;
    }


    /**
     * @return array<string, string>
     */
    public function getRouteArguments(): array
    {
        $routeInfoAttribute = $this->getAttribute(self::ROUTE_INFO_ATTRIBUTE);

        if (is_array($routeInfoAttribute) && isset($routeInfoAttribute[2])) {
            return $routeInfoAttribute[2];
        }

        return [];
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
