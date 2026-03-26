<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Response;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

/**
 * Backwards-compatible alias for Psr\Http\Message\ResponseInterface.
 *
 * Kept so that existing route handlers and middleware in platform-backend
 * do not need a mass rename. New code should type-hint ResponseInterface from PSR-7 directly.
 *
 * @deprecated Use Psr\Http\Message\ResponseInterface directly.
 */
interface ResponseInterface extends PsrResponseInterface
{
}
