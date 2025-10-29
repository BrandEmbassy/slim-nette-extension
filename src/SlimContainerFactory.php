<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim;

use BrandEmbassy\Slim\Request\RequestFactory;
use BrandEmbassy\Slim\Response\ResponseFactory;
use Slim\Container;
use Slim\Interfaces\RouterInterface;

/**
 * @final
 */
class SlimContainerFactory
{
    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var RequestFactory
     */
    private $requestFactory;

    /**
     * @var RouterInterface
     */
    private $router;


    public function __construct(
        ResponseFactory $responseFactory,
        RequestFactory $requestFactory,
        RouterInterface $router
    ) {
        $this->responseFactory = $responseFactory;
        $this->requestFactory = $requestFactory;
        $this->router = $router;
    }


    /**
     * @param array<string, mixed> $configuration
     */
    public function create(array $configuration): Container
    {
        if (!isset($configuration['response'])) {
            $configuration['response'] = $this->responseFactory->create();
        }
        if (!isset($configuration['request'])) {
            $configuration['request'] = $this->requestFactory->create();
        }
        if (!isset($configuration['router'])) {
            $configuration['router'] = $this->router;
        }
        // Prevent Slim from throwing on unexpected output buffer contents during finalize
        // by defaulting addContentLengthHeader to false unless explicitly provided.
        if (!isset($configuration['settings']['addContentLengthHeader'])) {
            $configuration['settings']['addContentLengthHeader'] = false;
        }

        return new Container($configuration);
    }
}
