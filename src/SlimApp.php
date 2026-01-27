<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Interfaces\RouteCollectorInterface;
use Throwable;
use function reset;

class SlimApp extends App
{
    protected ?ContainerInterface $container;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        ?ContainerInterface $container = null
    ) {
        parent::__construct($responseFactory, $container);
        $this->container = $container;
    }

    /**
     * Run the application and return the response
     * This method is for backward compatibility with Slim 3 style
     * where run() returned a ResponseInterface
     *
     * @throws Throwable
     */
    public function runAndReturnResponse(?ServerRequestInterface $request = null): ResponseInterface
    {
        if (!$request) {
            $serverRequestCreator = \Slim\Factory\ServerRequestCreatorFactory::create();
            $request = $serverRequestCreator->createServerRequestFromGlobals();
        }

        $response = $this->handle($request);

        $contentTypes = $response->getHeader('Content-Type');
        $contentType = reset($contentTypes);

        if ($contentType === 'text/html; charset=UTF-8' && $response->getBody()->getSize() === 0) {
            $response = $response->withHeader('Content-Type', 'text/plain; charset=UTF-8');
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
     * Inherits from parent class, just documenting for clarity
     */
}
