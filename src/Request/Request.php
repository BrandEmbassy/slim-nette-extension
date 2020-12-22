<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Request;

use BrandEmbassy\DateTime\DateTimeFromString;
use DateTime;
use DateTimeImmutable;
use InvalidArgumentException;
use Nette\Utils\Strings;
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

        return DateTimeFromString::create($format, $datetimeParam);
    }


    public function isHtml(): bool
    {
        $acceptHeader = $this->getHeaderLine('accept');

        return Strings::contains($acceptHeader, 'html');
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
}
