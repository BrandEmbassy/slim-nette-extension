<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Route\Route;
use Psr\Http\Message\ResponseInterface;
use function assert;

/**
 * @final
 */
class CreateChannelUserRoute implements Route
{
    private ?RequestInterface $request = null;


    public function __invoke(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $this->request = $request;

        return $response;
    }


    public function getRequest(): RequestInterface
    {
        $request = $this->request;
        assert($request instanceof RequestInterface);

        return $request;
    }
}
