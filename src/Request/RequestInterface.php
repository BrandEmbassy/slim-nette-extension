<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Request;

use DateTimeImmutable;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

interface RequestInterface extends ServerRequestInterface
{
    /**
     * @param string $key
     * @param string|int|null $default
     * @return string|integer|mixed[]|null
     */
    public function getQueryParam(string $key, $default = null);


    /**
     * @param string $name
     * @return string|integer
     */
    public function getRequiredArgument(string $name);


    /**
     * @param string $name
     * @return mixed[]|stdClass|string
     */
    public function getField(string $name);


    /**
     * @param string          $name
     * @param string|int|null $default
     * @return mixed[]|stdClass|string|integer|null
     */
    public function getOptionalField(string $name, $default = null);


    public function hasField(string $name): bool;


    /**
     * @return mixed[]|stdClass
     */
    public function getDecodedJsonFromBody();


    public function getDateTimeQueryParam(string $key): DateTimeImmutable;
}
