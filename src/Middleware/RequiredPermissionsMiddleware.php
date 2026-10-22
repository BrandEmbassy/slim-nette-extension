<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Middleware;

use BrandEmbassy\Slim\Route\RouteDefinition;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @final
 *
 * Exposes the route's declared permissions as a request attribute; checking them is up to the application.
 */
class RequiredPermissionsMiddleware implements MiddlewareInterface
{
    /**
     * @var string[]
     */
    private array $permissions;


    /**
     * @param string[] $permissions
     */
    public function __construct(array $permissions)
    {
        $this->permissions = $permissions;
    }


    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle(
            $request->withAttribute(RouteDefinition::REQUIRED_PERMISSIONS_ATTRIBUTE, $this->permissions),
        );
    }
}
