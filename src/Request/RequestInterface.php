<?php declare(strict_types = 1);

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
    public function getQueryParam(string $key, $default = null);

    public function getRequiredArgument(string $name): string;

    /**
     * @param string $name
     * @return array|stdClass|string
     */
    public function getField(string $name);

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getOptionalField(string $name, $default = null);

    public function hasField(string $name): bool;

    /**
     * @return mixed
     */
    public function getDecodedJsonFromBody();

    public function getDateTimeQueryParam(string $key): DateTimeImmutable;

}
