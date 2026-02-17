<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Request;

use Adbar\Dot;
use DateTime;
use DateTimeImmutable;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Slim\Route;
use function array_key_exists;
use function assert;
use function is_array;
use function is_string;
use function sprintf;
use function str_contains;

/**
 * @final
 */
class Request implements RequestInterface
{
    private const ROUTE_INFO_ATTRIBUTE = 'routeInfo';

    private const ROUTE_ATTRIBUTE = 'route';

    /**
     * @var Dot<string, mixed[]>|null
     *
     * @phpstan-ignore property.unusedType
     */
    private ?Dot $dotAnnotatedRequestBody = null;

    private ServerRequestInterface $request;


    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }


    public function getInnerRequest(): ServerRequestInterface
    {
        return $this->request;
    }


    public function getRoute(): Route
    {
        $route = $this->request->getAttribute(self::ROUTE_ATTRIBUTE);
        assert($route instanceof Route);

        return $route;
    }


    /**
     * @return array<string, string>
     */
    public function getRouteArguments(): array
    {
        $routeInfoAttribute = $this->request->getAttribute(self::ROUTE_INFO_ATTRIBUTE);

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


    /**
     * PSR-7 ServerRequestInterface delegation methods
     */
    public function getProtocolVersion(): string
    {
        return $this->request->getProtocolVersion();
    }


    /**
     * @param string $version
     *
     * @return static
     */
    public function withProtocolVersion($version)
    {
        $clone = clone $this;
        $clone->request = $this->request->withProtocolVersion($version);
        $clone->dotAnnotatedRequestBody = null;

        return $clone;
    }


    /**
     * @return string[][]
     */
    public function getHeaders(): array
    {
        return $this->request->getHeaders();
    }


    /**
     * @param string $name
     */
    public function hasHeader($name): bool
    {
        return $this->request->hasHeader($name);
    }


    /**
     * @param string $name
     *
     * @return string[]
     */
    public function getHeader($name): array
    {
        return $this->request->getHeader($name);
    }


    /**
     * @param string $name
     */
    public function getHeaderLine($name): string
    {
        return $this->request->getHeaderLine($name);
    }


    /**
     * @param string $name
     * @param string|string[] $value
     *
     * @return static
     */
    public function withHeader($name, $value)
    {
        $clone = clone $this;
        $clone->request = $this->request->withHeader($name, $value);
        $clone->dotAnnotatedRequestBody = null;

        return $clone;
    }


    /**
     * @param string $name
     * @param string|string[] $value
     *
     * @return static
     */
    public function withAddedHeader($name, $value)
    {
        $clone = clone $this;
        $clone->request = $this->request->withAddedHeader($name, $value);
        $clone->dotAnnotatedRequestBody = null;

        return $clone;
    }


    /**
     * @param string $name
     *
     * @return static
     */
    public function withoutHeader($name)
    {
        $clone = clone $this;
        $clone->request = $this->request->withoutHeader($name);
        $clone->dotAnnotatedRequestBody = null;

        return $clone;
    }


    public function getBody(): StreamInterface
    {
        return $this->request->getBody();
    }


    /**
     * @return static
     */
    public function withBody(StreamInterface $body)
    {
        $clone = clone $this;
        $clone->request = $this->request->withBody($body);
        $clone->dotAnnotatedRequestBody = null;

        return $clone;
    }


    public function getRequestTarget(): string
    {
        return $this->request->getRequestTarget();
    }


    /**
     * @param mixed $requestTarget
     *
     * @return static
     */
    public function withRequestTarget($requestTarget)
    {
        $clone = clone $this;
        $clone->request = $this->request->withRequestTarget($requestTarget);
        $clone->dotAnnotatedRequestBody = null;

        return $clone;
    }


    public function getMethod(): string
    {
        return $this->request->getMethod();
    }


    /**
     * @param string $method
     *
     * @return static
     */
    public function withMethod($method)
    {
        $clone = clone $this;
        $clone->request = $this->request->withMethod($method);
        $clone->dotAnnotatedRequestBody = null;

        return $clone;
    }


    public function getUri(): UriInterface
    {
        return $this->request->getUri();
    }


    /**
     * @param bool $preserveHost
     *
     * @return static
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $clone = clone $this;
        $clone->request = $this->request->withUri($uri, $preserveHost);
        $clone->dotAnnotatedRequestBody = null;

        return $clone;
    }


    /**
     * @return string[][]
     */
    public function getServerParams(): array
    {
        return $this->request->getServerParams();
    }


    /**
     * @param mixed $default
     *
     * @return mixed
     */
    public function getServerParam(string $key, $default = null)
    {
        $serverParams = $this->getServerParams();

        return $serverParams[$key] ?? $default;
    }


    /**
     * @return mixed[]
     */
    public function getCookieParams(): array
    {
        return $this->request->getCookieParams();
    }


    /**
     * @param mixed[] $cookies
     *
     * @return static
     */
    public function withCookieParams(array $cookies)
    {
        $clone = clone $this;
        $clone->request = $this->request->withCookieParams($cookies);
        $clone->dotAnnotatedRequestBody = null;

        return $clone;
    }


    /**
     * @return mixed[]
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
        $queryParams = $this->getQueryParams();

        return $queryParams[$key] ?? $default;
    }


    /**
     * @param mixed[] $query
     *
     * @return static
     */
    public function withQueryParams(array $query)
    {
        $clone = clone $this;
        $clone->request = $this->request->withQueryParams($query);
        $clone->dotAnnotatedRequestBody = null;

        return $clone;
    }


    /**
     * @return mixed[]
     */
    public function getUploadedFiles(): array
    {
        return $this->request->getUploadedFiles();
    }


    /**
     * @param mixed[] $uploadedFiles
     *
     * @return static
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $clone = clone $this;
        $clone->request = $this->request->withUploadedFiles($uploadedFiles);
        $clone->dotAnnotatedRequestBody = null;

        return $clone;
    }


    /**
     * @return mixed[]|object|null
     */
    public function getParsedBody()
    {
        return $this->request->getParsedBody();
    }


    /**
     * @param mixed[]|object|null $data
     *
     * @return static
     */
    public function withParsedBody($data)
    {
        $clone = clone $this;
        $clone->request = $this->request->withParsedBody($data);
        $clone->dotAnnotatedRequestBody = null;

        return $clone;
    }


    /**
     * @return mixed[]
     */
    public function getAttributes(): array
    {
        return $this->request->getAttributes();
    }


    /**
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
     * @param string $name
     * @param mixed $value
     *
     * @return static
     */
    public function withAttribute($name, $value)
    {
        $clone = clone $this;
        $clone->request = $this->request->withAttribute($name, $value);
        $clone->dotAnnotatedRequestBody = null;

        return $clone;
    }


    /**
     * @param string $name
     *
     * @return static
     */
    public function withoutAttribute($name)
    {
        $clone = clone $this;
        $clone->request = $this->request->withoutAttribute($name);
        $clone->dotAnnotatedRequestBody = null;

        return $clone;
    }
}
