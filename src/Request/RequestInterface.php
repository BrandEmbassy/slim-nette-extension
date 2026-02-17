<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Request;

use DateTimeImmutable;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Slim\Routing\Route;

interface RequestInterface
{
    /**
     * Get the inner PSR-7 ServerRequestInterface for operations
     * not covered by this interface.
     */
    public function getInnerRequest(): ServerRequestInterface;


    public function getMethod(): string;


    public function getUri(): UriInterface;


    /**
     * @return string[][]
     */
    public function getHeaders(): array;


    public function hasHeader(string $name): bool;


    /**
     * @return string[]
     */
    public function getHeader(string $name): array;


    public function getHeaderLine(string $name): string;


    public function getBody(): StreamInterface;


    /**
     * @return array<string, mixed>
     */
    public function getAttributes(): array;


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
    public function getAttribute($name, $default = null);


    /**
     * @param mixed $value
     *
     * @return static
     */
    public function withAttribute(string $name, $value);


    /**
     * @return mixed
     */
    public function getParsedBody();


    /**
     * @return string[]|string[][]
     */
    public function getQueryParams(): array;


    public function getRoute(): ?Route;


    /**
     * @return array<string, string>
     */
    public function getRouteArguments(): array;


    public function hasRouteArgument(string $argument): bool;


    public function getRouteArgument(string $argument): string;


    public function findRouteArgument(string $argument, ?string $default = null): ?string;


    /**
     * @return mixed[]
     */
    public function getParsedBodyAsArray(): array;


    /**
     * @return mixed
     */
    public function getField(string $name);


    /**
     * @param mixed $default
     *
     * @return mixed
     */
    public function findField(string $fieldName, $default = null);


    public function hasField(string $fieldName): bool;


    /**
     * @return string|string[]|null
     */
    public function findQueryParam(string $key, ?string $default = null);


    /**
     * @return string|string[]
     *
     * @throws QueryParamMissingException
     */
    public function getQueryParamStrict(string $key);


    public function findQueryParamAsString(string $key, ?string $default = null): ?string;


    /**
     * @throws QueryParamMissingException
     */
    public function getQueryParamAsString(string $key): string;


    public function hasAttribute(string $name): bool;


    /**
     * @param mixed $default
     *
     * @return mixed
     */
    public function findAttribute(string $name, $default = null);


    /**
     * @return mixed
     */
    public function getAttributeStrict(string $name);


    public function hasQueryParam(string $key): bool;


    public function getDateTimeQueryParam(string $key): DateTimeImmutable;


    public function isHtml(): bool;


    /**
     * @deprecated use getQueryParamStrict or findQueryParam
     *
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function getQueryParam(string $key, $default = null);
}
