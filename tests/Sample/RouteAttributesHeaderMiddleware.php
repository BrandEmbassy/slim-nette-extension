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
class RouteAttributesHeaderMiddleware implements Middleware
{
    public const REQUIRED_PERMISSIONS_HEADER = 'required-permissions';

    public const ALLOWED_AUTHORIZATIONS_HEADER = 'allowed-authorizations';


    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $requiredPermissions = $request->getAttribute(RouteDefinition::REQUIRED_PERMISSIONS_ATTRIBUTE);
        $allowedAuthorizations = $request->getAttribute(RouteDefinition::ALLOWED_AUTHORIZATIONS_ATTRIBUTE);

        return $next(
            $request,
            $response
                ->withHeader(self::REQUIRED_PERMISSIONS_HEADER, Json::encode($requiredPermissions))
                ->withHeader(self::ALLOWED_AUTHORIZATIONS_HEADER, Json::encode($allowedAuthorizations)),
        );
    }
}
