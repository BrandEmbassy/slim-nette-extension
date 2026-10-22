<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\Middleware\Middleware;
use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;
use BrandEmbassy\Slim\Route\RouteDefinition;
use Nette\Utils\Json;

/**
 * @final
 */
class RequiredPermissionsHeaderMiddleware implements Middleware
{
    public const HEADER_NAME = 'required-permissions';


    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $requiredPermissions = $request->getAttribute(RouteDefinition::REQUIRED_PERMISSIONS_ATTRIBUTE);

        return $next($request, $response->withHeader(self::HEADER_NAME, Json::encode($requiredPermissions)));
    }
}
