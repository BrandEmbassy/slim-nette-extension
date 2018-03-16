<?php

namespace BrandEmbassy\Slim\Request;

use DateTimeImmutable;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

interface RequestInterface extends ServerRequestInterface
{

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed|null
     */
    public function getQueryParam($key, $default = null);

    /**
     * @param string $name
     * @return string
     */
    public function getRequiredArgument($name);

    /**
     * @param string $name
     * @return array|stdClass|string
     */
    public function getField($name);

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getOptionalField($name, $default = null);

    /**
     * @param string $name
     * @return bool
     */
    public function hasField($name);

    /**
     * @return mixed
     */
    public function getDecodedJsonFromBody();

    /**
     * @param string $key
     * @return DateTimeImmutable
     */
    public function getDateTimeQueryParam($key);

}
