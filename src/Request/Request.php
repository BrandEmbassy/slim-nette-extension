<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Request;

use BrandEmbassy\DateTime\DateTimeFromString;
use DateTime;
use DateTimeImmutable;
use InvalidArgumentException;
use Slim\Http\Request as SlimRequest;
use Slim\Route;
use function array_key_exists;
use function assert;
use function is_array;
use function is_string;

/**
 * @method string[]|string[][] getQueryParams()
 * @method string|string[]|null getQueryParam(string $key, ?string $default = null)
 */
final class Request extends SlimRequest implements RequestInterface
{
    private const ROUTE_INFO_ATTRIBUTE = 'routeInfo';
    private const ROUTE_ATTRIBUTE = 'route';


    public function getRoute(): Route
    {
        $route = $this->getAttribute(self::ROUTE_ATTRIBUTE);
        assert($route instanceof Route);

        return $route;
    }


    /**
     * @return array<string, string>
     */
    public function getRouteAttributes(): array
    {
        $routeInfoAttribute = $this->getAttribute(self::ROUTE_INFO_ATTRIBUTE);

        if (is_array($routeInfoAttribute) && isset($routeInfoAttribute[2])) {
            return $routeInfoAttribute[2];
        }

        return [];
    }


    public function hasRouteAttribute(string $routeAttributeName): bool
    {
        return isset($this->getRouteAttributes()[$routeAttributeName]);
    }


    /**
     * @throws RouteAttributeMissingException
     */
    public function getRouteAttribute(string $routeAttributeName): string
    {
        if ($this->hasRouteAttribute($routeAttributeName)) {
            return $this->getRouteAttributes()[$routeAttributeName];
        }

        throw RouteAttributeMissingException::create($routeAttributeName);
    }


    public function findRouteAttribute(string $routeAttributeName, ?string $default = null): ?string
    {
        return $this->getRouteAttributes()[$routeAttributeName] ?? $default;
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
    public function getField(string $fieldFieldName)
    {
        if ($this->hasField($fieldFieldName)) {
            return $this->getParsedBodyAsArray()[$fieldFieldName];
        }

        throw RequestFieldMissingException::create($fieldFieldName);
    }


    /**
     * @param mixed $default
     *
     * @return mixed
     */
    public function findField(string $fieldFieldName, $default = null)
    {
        return $this->getParsedBodyAsArray()[$fieldFieldName] ?? $default;
    }


    public function hasField(string $fieldName): bool
    {
        return array_key_exists($fieldName, $this->getParsedBodyAsArray());
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
            return $this->getQueryParams()[$key];
        }

        throw QueryParamMissingException::create($key);
    }


    public function hasQueryParam(string $key): bool
    {
        return isset($this->getQueryParams()[$key]);
    }


    /**
     * @throws QueryParamMissingException
     * @throws InvalidArgumentException
     */
    public function getDateTimeQueryParam(string $field, string $format = DateTime::ATOM): DateTimeImmutable
    {
        $datetimeParam = $this->getQueryParamStrict($field);
        assert(is_string($datetimeParam));

        return DateTimeFromString::create($format, $datetimeParam);
    }
}
