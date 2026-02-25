<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Request;

use Psr\Http\Message\ServerRequestInterface;
use Slim\Routing\Route;

/**
 * Extension of PSR-7 ServerRequestInterface with convenience methods
 * for route access and the deprecated getQueryParam.
 *
 * All standard PSR-7 methods are inherited from ServerRequestInterface.
 * Use getInnerRequest() for any PSR-7 operations not exposed here.
 */
interface RequestInterface extends ServerRequestInterface
{
    public function getInnerRequest(): ServerRequestInterface;


    public function getRoute(): ?Route;


    /**
     * @return array<string, string>
     */
    public function getRouteArguments(): array;


    public function hasRouteArgument(string $argument): bool;


    public function getRouteArgument(string $argument): string;


    public function findRouteArgument(string $argument, ?string $default = null): ?string;


    /**
     * @deprecated use getQueryParams() from PSR-7 directly
     *
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function getQueryParam(string $key, $default = null);
}
