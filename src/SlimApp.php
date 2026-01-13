<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface;
use Throwable;
use function reset;

class SlimApp extends App
{
    private ?ContainerInterface $container;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        ?ContainerInterface $container = null
    ) {
        parent::__construct($responseFactory, $container);
        $this->container = $container;
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param bool $silent
     *
     * @throws Throwable
     */
    public function run($silent = false): ResponseInterface
    {
        $response = parent::run(true);

        $contentTypes = $response->getHeader('Content-Type');
        $contentType = reset($contentTypes);

        if ($contentType === 'text/html; charset=UTF-8' && $response->getBody()->getSize() === 0) {
            $response = $response->withHeader('Content-Type', 'text/plain; charset=UTF-8');
        }

        if (!$silent) {
            $this->respond($response);
        }

        return $response;
    }

    /**
     * Backward compatibility method for Slim 3
     * In Slim 4, container is accessed via getContainer()
     */
    public function getContainer(): ?ContainerInterface
    {
        return $this->container;
    }

    /**
     * Get the route collector (replaces the old router access)
     */
    public function getRouteCollector(): RouteCollectorProxyInterface
    {
        return $this;
    }
}
