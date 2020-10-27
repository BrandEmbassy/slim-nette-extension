<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Request;

use DateTimeImmutable;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Route;

interface RequestInterface extends ServerRequestInterface
{
    public function getRoute(): Route;


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
    public function getField(string $fieldName);


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


    /**
     * @deprecated use findAttribute or findRouteArgument
     *
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */
    public function findAttributeOrRouteArgument(string $name, $default = null);


    public function hasQueryParam(string $key): bool;


    public function getDateTimeQueryParam(string $key): DateTimeImmutable;


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
