<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassyTest\Slim\Tools\JsonResponseTestTool;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

/**
 * Intentionally not extending ErrorHandler. Slim does not call this with 3rd param at __invoke method.
 *
 * @final
 */
class NotAllowedHandler
{
    public function __invoke(RequestInterface $request, PsrResponseInterface $response): PsrResponseInterface
    {
        return JsonResponseTestTool::from($response, ['error' => 'Sample NotAllowedHandler here!'], 405);
    }
}
