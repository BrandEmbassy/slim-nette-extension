<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Route;

use BrandEmbassy\Slim\Request\RequestDecorator;
use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseDecorator;
use BrandEmbassy\Slim\Response\ResponseInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @final
 *
 * Adapts a legacy Route callable (RequestInterface, ResponseInterface)
 * to Slim 4's route handler signature (ServerRequestInterface, ResponseInterface, args).
 */
class LegacyRouteAdapter
{
    private Route $route;


    public function __construct(Route $route)
    {
        $this->route = $route;
    }


    /**
     * @param array<string, string> $args
     */
    public function __invoke(
        ServerRequestInterface $request,
        PsrResponseInterface $response,
        array $args,
    ): PsrResponseInterface {
        foreach ($args as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        return ($this->route)(
            self::wrapRequest($request),
            self::wrapResponse($response),
        );
    }


    private static function wrapRequest(ServerRequestInterface $request): RequestInterface
    {
        if ($request instanceof RequestInterface) {
            return $request;
        }

        return new RequestDecorator($request);
    }


    private static function wrapResponse(PsrResponseInterface $response): ResponseInterface
    {
        if ($response instanceof ResponseInterface) {
            return $response;
        }

        return new ResponseDecorator($response);
    }
}
