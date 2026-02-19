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
class ListChannelsRoute implements Route
{
    public function __invoke(RequestInterface $request, ResponseInterface $response): PsrResponseInterface
    {
        return JsonResponseTestTool::from(
            $response,
            [
                [
                    'id' => 1,
                    'name' => 'First channel',
                ],
                [
                    'id' => 2,
                    'name' => 'Second channel',
                ],
            ],
            200,
        );
    }
}
