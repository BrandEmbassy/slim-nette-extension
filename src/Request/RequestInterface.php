<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Request;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Backwards-compatible alias for ServerRequestInterface.
 *
 * Kept so that existing route handlers and middleware in platform-backend
 * do not need a mass rename. New code should type-hint ServerRequestInterface directly.
 *
 * @deprecated Use Psr\Http\Message\ServerRequestInterface directly.
 */
interface RequestInterface extends ServerRequestInterface
{
}
