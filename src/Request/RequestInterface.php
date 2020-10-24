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
    public function getRouteAttributes(): array;


    public function hasRouteAttribute(string $routeAttributeName): bool;


    public function getRouteAttribute(string $routeAttributeName): string;


    public function findRouteAttribute(string $routeAttributeName, ?string $default = null): ?string;


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


    public function hasQueryParam(string $key): bool;


    public function getDateTimeQueryParam(string $key): DateTimeImmutable;
}
