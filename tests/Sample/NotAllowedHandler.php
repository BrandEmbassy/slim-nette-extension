<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\JsonResponse;
use BrandEmbassy\Slim\Response\ResponseInterface;

/**
 * Intentionally not extending ErrorHandler. Slim does not call this with 3rd param at __invoke method.
 *
 * @final
 */
class NotAllowedHandler
{
    public function __invoke(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return JsonResponse::from($response, ['error' => 'Sample NotAllowedHandler here!'], 405);
    }
}
