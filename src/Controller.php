<?php

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

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function beforeAction(RequestInterface $request, ResponseInterface $response)
    {
        // intentionally - this method is empty in default
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function afterAction(RequestInterface $request, ResponseInterface $response)
    {
        // intentionally - this method is empty in default
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param Route $next
     * @return ResponseInterface
     */
    public function middleware(RequestInterface $request, ResponseInterface $response, Route $next)
    {
        $this->beforeAction($request, $response);
        /** @var ResponseInterface $response */
        $response = $next($request, $response);
        $this->afterAction($request, $response);

        return $response;
    }

}
