<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Controller;

use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;
use Slim\Container;
use Slim\Route;
use function assert;

abstract class Controller extends Container
{
    public function beforeAction(RequestInterface $request, ResponseInterface $response): void
    {
        // intentionally - this method is empty in default
    }


    public function afterAction(RequestInterface $request, ResponseInterface $response): void
    {
        // intentionally - this method is empty in default
    }


    public function middleware(RequestInterface $request, ResponseInterface $response, Route $next): ResponseInterface
    {
        $this->beforeAction($request, $response);

        $routeResponse = $next($request, $response);
        assert($routeResponse instanceof ResponseInterface);

        $this->afterAction($request, $routeResponse);

        return $routeResponse;
    }
}
