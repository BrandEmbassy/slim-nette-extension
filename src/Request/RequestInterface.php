<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Request;

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
     * @return mixed[]
     */
    public function getParsedBodyAsArray(): array;


    /**
     * @deprecated use getQueryParams() from PSR-7
     *
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function getQueryParam(string $key, $default = null);
}
