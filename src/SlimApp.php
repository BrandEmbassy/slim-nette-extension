<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\ResponseEmitter;
use Throwable;
use function reset;

class SlimApp extends App
{
    private ?CompatibilityContainer $compatibilityContainer = null;


    public function setCompatibilityContainer(CompatibilityContainer $container): void
    {
        $this->compatibilityContainer = $container;
    }


    /**
     * Get the compatibility container for backward compatible access to router and settings.
     * This provides Slim 3 style access: $app->getContainer()->get('router'), $container['settings']
     */
    public function getCompatibilityContainer(): ?CompatibilityContainer
    {
        return $this->compatibilityContainer;
    }


    /**
     * Backward compatible run method.
     *
     * @param bool|ServerRequestInterface|null $silentOrRequest In Slim 3, this was a boolean $silent parameter.
     *                                                          For backward compatibility, passing true returns the response without emitting.
     *                                                          Can also accept a ServerRequestInterface for Slim 4 style usage.
     *
     * @throws Throwable
     */
    public function run($silentOrRequest = null): ResponseInterface
    {
        // Handle backward compatibility: if boolean true is passed, it means "silent mode" (don't emit)
        $silent = false;
        $request = null;

        if ($silentOrRequest === true) {
            $silent = true;
        } elseif ($silentOrRequest === false || $silentOrRequest === null) {
            $silent = false;
        } elseif ($silentOrRequest instanceof ServerRequestInterface) {
            $request = $silentOrRequest;
            $silent = true; // When request is provided, assume we just want the response
        }

        $response = $this->handle($request ?? $this->createRequest());

        $contentTypes = $response->getHeader('Content-Type');
        $contentType = reset($contentTypes);

        if ($contentType === 'text/html; charset=UTF-8' && $response->getBody()->getSize() === 0) {
            $response = $response->withHeader('Content-Type', 'text/plain; charset=UTF-8');
        }

        if (!$silent) {
            $responseEmitter = new ResponseEmitter();
            $responseEmitter->emit($response);
        }

        return $response;
    }


    /**
     * @throws Throwable
     */
    public function runAndEmit(?ServerRequestInterface $request = null): ResponseInterface
    {
        $response = $this->run($request);
        $responseEmitter = new ResponseEmitter();
        $responseEmitter->emit($response);

        return $response;
    }


    private function createRequest(): ServerRequestInterface
    {
        return \Slim\Factory\ServerRequestCreatorFactory::create()->createServerRequestFromGlobals();
    }
}
