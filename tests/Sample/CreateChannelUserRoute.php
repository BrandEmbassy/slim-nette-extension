<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use function assert;

/**
 * @final
 */
class CreateChannelUserRoute
{
    private ?ServerRequestInterface $request = null;


    /**
     * @param array<string, string> $args
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
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
