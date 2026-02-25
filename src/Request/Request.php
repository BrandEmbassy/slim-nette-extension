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
 * Wrapper around PSR-7 ServerRequestInterface providing convenience methods
 * for route access and the deprecated getQueryParam.
 *
 * Implements ServerRequestInterface (via RequestInterface), delegating all
 * PSR-7 methods to the wrapped inner request.
 */
class Request implements RequestInterface
{
    private ServerRequestInterface $request;


    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }


    public function getInnerRequest(): ServerRequestInterface
    {
        return $this->request;
    }


    public function getProtocolVersion(): string
    {
        return $this->request->getProtocolVersion();
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param string $version
     */
    public function withProtocolVersion($version): static
    {
        $clone = clone $this;
        $clone->request = $this->request->withProtocolVersion($version);

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
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param string $name
     */
    public function hasHeader($name): bool
    {
        return $this->request->hasHeader($name);
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param string $name
     *
     * @return string[]
     */
    public function getHeader($name): array
    {
        return $this->request->getHeader($name);
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param string $name
     */
    public function getHeaderLine($name): string
    {
        return $this->request->getHeaderLine($name);
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param string $name
     * @param string|string[] $value
     */
    public function withHeader($name, $value): static
    {
        $clone = clone $this;
        $clone->request = $this->request->withHeader($name, $value);

        return $clone;
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param string $name
     * @param string|string[] $value
     */
    public function withAddedHeader($name, $value): static
    {
        $clone = clone $this;
        $clone->request = $this->request->withAddedHeader($name, $value);

        return $clone;
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param string $name
     */
    public function withoutHeader($name): static
    {
        $clone = clone $this;
        $clone->request = $this->request->withoutHeader($name);

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

        return $clone;
    }


    public function getRequestTarget(): string
    {
        return $this->request->getRequestTarget();
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param string $requestTarget
     */
    public function withRequestTarget($requestTarget): static
    {
        $clone = clone $this;
        $clone->request = $this->request->withRequestTarget($requestTarget);

        return $clone;
    }


    public function getMethod(): string
    {
        return $this->request->getMethod();
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param string $method
     */
    public function withMethod($method): static
    {
        $clone = clone $this;
        $clone->request = $this->request->withMethod($method);

        return $clone;
    }


    public function getUri(): UriInterface
    {
        return $this->request->getUri();
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param bool $preserveHost
     */
    public function withUri(UriInterface $uri, $preserveHost = false): static
    {
        $clone = clone $this;
        $clone->request = $this->request->withUri($uri, $preserveHost);

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
     * @return array<string, string>
     */
    public function getCookieParams(): array
    {
        return $this->request->getCookieParams();
    }


    public function withCookieParams(array $cookies): static
    {
        $clone = clone $this;
        $clone->request = $this->request->withCookieParams($cookies);

        return $clone;
    }


    /**
     * @return string[]|string[][]
     */
    public function getQueryParams(): array
    {
        return $this->request->getQueryParams();
    }


    /**
     * @param array<string, string|string[]> $query
     */
    public function withQueryParams(array $query): static
    {
        $clone = clone $this;
        $clone->request = $this->request->withQueryParams($query);

        return $clone;
    }


    /**
     * @return array<string, \Psr\Http\Message\UploadedFileInterface>
     */
    public function getUploadedFiles(): array
    {
        return $this->request->getUploadedFiles();
    }


    public function withUploadedFiles(array $uploadedFiles): static
    {
        $clone = clone $this;
        $clone->request = $this->request->withUploadedFiles($uploadedFiles);

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
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param string $name
     * @param mixed $value
     */
    public function withAttribute($name, $value): static
    {
        $clone = clone $this;
        $clone->request = $this->request->withAttribute($name, $value);

        return $clone;
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param string $name
     */
    public function withoutAttribute($name): static
    {
        $clone = clone $this;
        $clone->request = $this->request->withoutAttribute($name);

        return $clone;
    }


    /**
     * Get routing results from Slim 4's RouteContext
     */
    public function getRoutingResults(): RoutingResults
    {
        return RouteContext::fromRequest($this->request)->getRoutingResults();
    }


    public function getRoute(): ?Route
    {
        $routeContext = RouteContext::fromRequest($this->request);
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
     * @deprecated use getQueryParams() from PSR-7 directly
     *
     * @param mixed|null $default
     *
     * @return string|string[]|null
     */
    public function getQueryParam(string $key, $default = null)
    {
        $params = $this->request->getQueryParams();

        return $params[$key] ?? $default;
    }
}
