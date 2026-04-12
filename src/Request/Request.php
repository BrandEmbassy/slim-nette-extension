<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Request;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Slim\Routing\Route;
use Slim\Routing\RouteContext;
use Slim\Routing\RoutingResults;

/**
 * @final
 *
 * Wraps a PSR-7 ServerRequest with convenience methods for common
 * request operations. All PSR-7 methods are delegated to the inner request.
 *
 * Uses composition instead of inheritance to avoid coupling to Slim's
 * internal PSR-7 implementation.
 */
class Request implements RequestInterface
{
    public function __construct(
        private readonly ServerRequestInterface $inner
    ) {
    }


    public function getRoute(): ?Route
    {
        $routeContext = RouteContext::fromRequest($this->inner);
        $route = $routeContext->getRoute();

        return $route instanceof Route ? $route : null;
    }


    /**
     * @return array<string, string>
     */
    public function getRouteArguments(): array
    {
        return $this->getRoutingResults()->getRouteArguments();
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
     * @deprecated use getQueryParams() from PSR-7
     *
     * @param mixed|null $default
     *
     * @return mixed
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


    /**
     * @return array<string, mixed>
     */
    public function getServerParams(): array
    {
        return $this->inner->getServerParams();
    }


    /**
     * @return array<string, string>
     */
    public function getCookieParams(): array
    {
        return $this->inner->getCookieParams();
    }


    /**
     * @param array<string, string> $cookies
     */
    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        return new self($this->inner->withCookieParams($cookies));
    }


    /**
     * @return array<string, mixed>
     */
    public function getQueryParams(): array
    {
        return $this->inner->getQueryParams();
    }


    /**
     * @param array<string, mixed> $query
     */
    public function withQueryParams(array $query): ServerRequestInterface
    {
        return new self($this->inner->withQueryParams($query));
    }


    /**
     * @return array<string, mixed>
     */
    public function getUploadedFiles(): array
    {
        return $this->inner->getUploadedFiles();
    }


    /**
     * @param array<string, mixed> $uploadedFiles
     */
    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
        return new self($this->inner->withUploadedFiles($uploadedFiles));
    }


    /**
     * @return array<string, mixed>|object|null
     */
    public function getParsedBody()
    {
        return $this->inner->getParsedBody();
    }


    /**
     * @param mixed $data
     */
    public function withParsedBody($data): ServerRequestInterface
    {
        return new self($this->inner->withParsedBody($data));
    }


    /**
     * @return array<string, mixed>
     */
    public function getAttributes(): array
    {
        return $this->inner->getAttributes();
    }


    /**
     * @param mixed $default
     */
    public function getAttribute(string $name, $default = null)
    {
        return $this->inner->getAttribute($name, $default);
    }


    /**
     * @param mixed $value
     */
    public function withAttribute(string $name, $value): ServerRequestInterface
    {
        return new self($this->inner->withAttribute($name, $value));
    }


    public function withoutAttribute(string $name): ServerRequestInterface
    {
        return new self($this->inner->withoutAttribute($name));
    }


    public function getRequestTarget(): string
    {
        return $this->inner->getRequestTarget();
    }


    public function withRequestTarget(string $requestTarget): static
    {
        return new self($this->inner->withRequestTarget($requestTarget));
    }


    public function getMethod(): string
    {
        return $this->inner->getMethod();
    }


    public function withMethod(string $method): static
    {
        return new self($this->inner->withMethod($method));
    }


    public function getUri(): UriInterface
    {
        return $this->inner->getUri();
    }


    public function withUri(UriInterface $uri, bool $preserveHost = false): static
    {
        return new self($this->inner->withUri($uri, $preserveHost));
    }


    public function getProtocolVersion(): string
    {
        return $this->inner->getProtocolVersion();
    }


    public function withProtocolVersion(string $version): static
    {
        return new self($this->inner->withProtocolVersion($version));
    }


    /**
     * @return array<string, array<string>>
     */
    public function getHeaders(): array
    {
        return $this->inner->getHeaders();
    }


    public function hasHeader(string $name): bool
    {
        return $this->inner->hasHeader($name);
    }


    /**
     * @return array<string>
     */
    public function getHeader(string $name): array
    {
        return $this->inner->getHeader($name);
    }


    public function getHeaderLine(string $name): string
    {
        return $this->inner->getHeaderLine($name);
    }


    /**
     * @param string|array<string> $value
     */
    public function withHeader(string $name, $value): static
    {
        return new self($this->inner->withHeader($name, $value));
    }


    /**
     * @param string|array<string> $value
     */
    public function withAddedHeader(string $name, $value): static
    {
        return new self($this->inner->withAddedHeader($name, $value));
    }


    public function withoutHeader(string $name): static
    {
        return new self($this->inner->withoutHeader($name));
    }


    public function getBody(): StreamInterface
    {
        return $this->inner->getBody();
    }


    public function withBody(StreamInterface $body): static
    {
        return new self($this->inner->withBody($body));
    }


    private function getRoutingResults(): RoutingResults
    {
        return RouteContext::fromRequest($this->inner)->getRoutingResults();
    }
}
