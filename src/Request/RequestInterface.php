<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Request;

use DateTimeImmutable;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Routing\Route;

interface RequestInterface extends ServerRequestInterface
{
    /**
     * Get the matched route.
     */
    public function getRoute(): ?Route;


    /**
     * @return array<string, string>
     */
    public function getRouteArguments(): array;


    public function hasRouteArgument(string $argument): bool;


    public function getRouteArgument(string $argument): string;


    public function findRouteArgument(string $argument, ?string $default = null): ?string;


    /**
     * @deprecated Will be removed in v6. Use PSR-7 getParsedBody() and cast to array instead.
     *
     * @return mixed[]
     */
    public function getParsedBodyAsArray(): array;


    /**
     * @deprecated Will be removed in v6. Use PSR-7 getParsedBody() with dot-notation access instead.
     *
     * @return mixed
     */
    public function getField(string $name);


    /**
     * @deprecated Will be removed in v6. Use PSR-7 getParsedBody() with dot-notation access instead.
     *
     * @param mixed $default
     *
     * @return mixed
     */
    public function findField(string $fieldName, $default = null);


    /**
     * @deprecated Will be removed in v6. Use PSR-7 getParsedBody() with dot-notation access instead.
     */
    public function hasField(string $fieldName): bool;


    /**
     * @deprecated Will be removed in v6. Use PSR-7 getQueryParams() instead.
     *
     * @return string|string[]|null
     */
    public function findQueryParam(string $key, ?string $default = null);


    /**
     * @deprecated Will be removed in v6. Use PSR-7 getQueryParams() instead.
     *
     * @return string|string[]
     *
     * @throws QueryParamMissingException
     */
    public function getQueryParamStrict(string $key);


    /**
     * @deprecated Will be removed in v6. Use PSR-7 getQueryParams() instead.
     */
    public function findQueryParamAsString(string $key, ?string $default = null): ?string;


    /**
     * @deprecated Will be removed in v6. Use PSR-7 getQueryParams() instead.
     *
     * @throws QueryParamMissingException
     */
    public function getQueryParamAsString(string $key): string;


    /**
     * @deprecated Will be removed in v6. Use PSR-7 getAttributes() with array_key_exists() instead.
     */
    public function hasAttribute(string $name): bool;


    /**
     * @deprecated Will be removed in v6. Use PSR-7 getAttribute() instead.
     *
     * @param mixed $default
     *
     * @return mixed
     */
    public function findAttribute(string $name, $default = null);


    /**
     * @deprecated Will be removed in v6. Use PSR-7 getAttribute() instead.
     *
     * @return mixed
     */
    public function getAttributeStrict(string $name);


    /**
     * @deprecated Will be removed in v6. Use PSR-7 getQueryParams() with array_key_exists() instead.
     */
    public function hasQueryParam(string $key): bool;


    /**
     * @deprecated Will be removed in v6. Parse datetime from getQueryParams() directly.
     */
    public function getDateTimeQueryParam(string $key): DateTimeImmutable;


    /**
     * @deprecated Will be removed in v6. Check Accept header via getHeaderLine('Accept') instead.
     */
    public function isHtml(): bool;


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
     * @deprecated use getQueryParamStrict or findQueryParam
     *
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function getQueryParam(string $key, $default = null);
}
