<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim;

use BrandEmbassy\Slim\Request\Request;
use BrandEmbassy\Slim\Response\Response;
use BrandEmbassy\Slim\Response\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Throwable;

/**
 * @final
 *
 * Bridges Slim 4's error handler signature to Slim 3-style error handlers
 * for backward compatibility.
 */
class ErrorHandlerBridge
{
    /**
     * @param array<string, callable> $handlers
     */
    public function __construct(
        private readonly array $handlers,
    ) {
    }


    // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails,
    ): ResponseInterface {
        $response = new Response();
        $wrappedRequest = new Request($request);

        if ($exception instanceof HttpNotFoundException && isset($this->handlers['notFoundHandler'])) {
            return ($this->handlers['notFoundHandler'])($wrappedRequest, $response->withStatus(404), $exception);
        }

        if ($exception instanceof HttpMethodNotAllowedException && isset($this->handlers['notAllowedHandler'])) {
            return ($this->handlers['notAllowedHandler'])($wrappedRequest, $response->withStatus(405), $exception);
        }

        if (isset($this->handlers['errorHandler'])) {
            return ($this->handlers['errorHandler'])($wrappedRequest, $response->withStatus(500), $exception);
        }

        $response->getBody()->write('Internal Server Error');

        return $response->withStatus(500);
    }
}
