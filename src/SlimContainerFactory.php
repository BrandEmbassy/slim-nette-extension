<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim;

use BrandEmbassy\Slim\Request\RequestFactory;
use BrandEmbassy\Slim\Response\ResponseFactory;
use Slim\Container;

final class SlimContainerFactory
{
    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var RequestFactory
     */
    private $requestFactory;


    public function __construct(ResponseFactory $responseFactory, RequestFactory $requestFactory)
    {
        $this->responseFactory = $responseFactory;
        $this->requestFactory = $requestFactory;
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

        return new Container($configuration);
    }
}
