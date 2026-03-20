<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Route;

use BrandEmbassy\Slim\Request\Request;
use BrandEmbassy\Slim\Response\Response;
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
        ServerRequestInterface $psrRequest,
        PsrResponseInterface $psrResponse,
        array $args,
    ): PsrResponseInterface {
        $request = new Request($psrRequest);
        $response = $psrResponse instanceof ResponseInterface ? $psrResponse : new Response();

        return ($this->route)($request, $response);
    }
}
