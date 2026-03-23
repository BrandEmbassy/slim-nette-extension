<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use function assert;
use function reset;

class SlimApp extends App
{
    /**
     * We always construct SlimApp with a container, so this override
     * narrows the nullable return type from the parent.
     */
    public function getContainer(): ContainerInterface
    {
        $container = parent::getContainer();
        assert($container !== null);

        return $container;
    }


    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = parent::handle($request);

        $contentTypes = $response->getHeader('Content-Type');
        $contentType = reset($contentTypes);

        if ($contentType === 'text/html; charset=UTF-8' && $response->getBody()->getSize() === 0) {
            return $response->withHeader('Content-Type', 'text/plain; charset=UTF-8');
        }

        return $response;
    }
}
