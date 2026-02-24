<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\JsonResponse;
use BrandEmbassy\Slim\Response\ResponseInterface;
use BrandEmbassy\Slim\Route\Route;

/**
 * @final
 */
class CreateChannelRoute implements Route
{
    public function __invoke(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return JsonResponse::from($response, ['status' => 'created'], 201);
    }
}
