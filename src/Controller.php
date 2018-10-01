<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim;

use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;
use Slim\Container;
use Slim\Route;

/**
 * @deprecated Use Invokable MiddlewareInterface
 */
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
        /** @var ResponseInterface $response */
        $response = $next($request, $response);
        $this->afterAction($request, $response);

        return $response;
    }

}
