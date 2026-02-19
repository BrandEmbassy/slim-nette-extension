<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;
use BrandEmbassy\Slim\Route\Route;
use BrandEmbassyTest\Slim\Tools\JsonResponseTestTool;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

/**
 * @final
 */
class CreateChannelRoute implements Route
{
    public function __invoke(RequestInterface $request, ResponseInterface $response): PsrResponseInterface
    {
        return JsonResponseTestTool::from($response, ['status' => 'created'], 201);
    }
}
