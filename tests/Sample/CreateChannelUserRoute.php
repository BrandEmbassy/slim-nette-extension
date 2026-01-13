<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\Route\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @final
 */
class CreateChannelUserRoute implements Route
{
    private ?ServerRequestInterface $request = null;


    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $this->request = $request;

        return $response;
    }


    public function getRequest(): ServerRequestInterface
    {
        $request = $this->request;
        assert($request instanceof ServerRequestInterface);

        return $request;
    }
}
