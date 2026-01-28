<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Request;

use Adbar\Dot;
use DateTime;
use DateTimeImmutable;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
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
 * Wrapper around PSR-7 ServerRequestInterface to provide backward compatibility
 * with Slim 3 request methods while using Slim 4.
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
     * Get the matched route for backward compatibility with Slim 3
     * In Slim 4, routes are accessed via RouteContext
     */
    public function getRoute(): ?Route
    {
        $routeContext = RouteContext::fromRequest($this->request);
        $route = $routeContext->getRoute();

        // PHPStan: RouteContext::getRoute() returns RouteInterface|null but we need Route|null
        // At runtime, this will always be Route|null in Slim 4
        return $route instanceof \Slim\Routing\Route ? $route : null;
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
     * @return Dot<string, mixed[]>
     */
    private function getDotAnnotatedRequestBody(): Dot
    {
        if ($this->dotAnnotatedRequestBody === null) {
            $this->dotAnnotatedRequestBody = new Dot($this->getParsedBodyAsArray());
        }

        return $this->dotAnnotatedRequestBody;
    }


    // PSR-7 ServerRequestInterface implementation - delegate to inner request

    public function getProtocolVersion(): string
    {
        return $this->request->getProtocolVersion();
    }


    public function withProtocolVersion(string $version): static
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


    public function hasHeader(string $name): bool
    {
        return $this->request->hasHeader($name);
    }


    /**
     * @return string[]
     */
    public function getHeader(string $name): array
    {
        return $this->request->getHeader($name);
    }


    public function getHeaderLine(string $name): string
    {
        return $this->request->getHeaderLine($name);
    }


    /**
     * @param string|string[] $value
     */
    public function withHeader(string $name, $value): static
    {
        $clone = clone $this;
        $clone->request = $this->request->withHeader($name, $value);
        $clone->dotAnnotatedRequestBody = null;

        return $clone;
    }


    /**
     * @param string|string[] $value
     */
    public function withAddedHeader(string $name, $value): static
    {
        $clone = clone $this;
        $clone->request = $this->request->withAddedHeader($name, $value);
        $clone->dotAnnotatedRequestBody = null;

        return $clone;
    }


    public function withoutHeader(string $name): static
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


    public function withBody(StreamInterface $body): static
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


    public function withRequestTarget(string $requestTarget): static
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


    public function withMethod(string $method): static
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


    public function withUri(UriInterface $uri, bool $preserveHost = false): static
    {
        $clone = clone $this;
        $clone->request = $this->request->withUri($uri, $preserveHost);
        $clone->dotAnnotatedRequestBody = null;

        return $clone;
    }


    /**
     * @return array<string, mixed>
     */
    public function getServerParams(): array
    {
        return $this->request->getServerParams();
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
     * @return array<string, mixed>
     */
    public function getCookieParams(): array
    {
        return $this->request->getCookieParams();
    }


    /**
     * @param array<string, mixed> $cookies
     */
    public function withCookieParams(array $cookies): static
    {
        $clone = clone $this;
        $clone->request = $this->request->withCookieParams($cookies);
        $clone->dotAnnotatedRequestBody = null;

        return $clone;
    }


    /**
     * @param array<string, mixed> $query
     */
    public function withQueryParams(array $query): static
    {
        $clone = clone $this;
        $clone->request = $this->request->withQueryParams($query);
        $clone->dotAnnotatedRequestBody = null;

        return $clone;
    }


    /**
     * @return array<string, mixed>
     */
    public function getUploadedFiles(): array
    {
        return $this->request->getUploadedFiles();
    }


    /**
     * @param array<string, mixed> $uploadedFiles
     */
    public function withUploadedFiles(array $uploadedFiles): static
    {
        $clone = clone $this;
        $clone->request = $this->request->withUploadedFiles($uploadedFiles);
        $clone->dotAnnotatedRequestBody = null;

        return $clone;
    }


    /**
     * @return mixed
     */
    public function getParsedBody()
    {
        return $this->request->getParsedBody();
    }


    /**
     * @param mixed $data
     */
    public function withParsedBody($data): static
    {
        $clone = clone $this;
        $clone->request = $this->request->withParsedBody($data);
        $clone->dotAnnotatedRequestBody = null;

        return $clone;
    }


    /**
     * @return array<string, mixed>
     */
    public function getAttributes(): array
    {
        return $this->request->getAttributes();
    }


    /**
     * @param mixed $default
     *
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        return $this->request->getAttribute($name, $default);
    }


    /**
     * @param mixed $value
     */
    public function withAttribute(string $name, $value): static
    {
        $clone = clone $this;
        $clone->request = $this->request->withAttribute($name, $value);
        $clone->dotAnnotatedRequestBody = null;

        return $clone;
    }


    public function withoutAttribute(string $name): static
    {
        $clone = clone $this;
        $clone->request = $this->request->withoutAttribute($name);
        $clone->dotAnnotatedRequestBody = null;

        return $clone;
    }
}
