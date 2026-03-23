<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Route;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @final
 *
 * Adapts a legacy Route callable (ServerRequestInterface, ResponseInterface)
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
        ResponseInterface $response,
        array $args,
    ): ResponseInterface {
        foreach ($args as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        return ($this->route)($request, $response);
    }
}
