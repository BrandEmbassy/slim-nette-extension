<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim;

use BrandEmbassy\Slim\Response\ResponseFactory;
use Slim\Container;

final class SlimContainerFactory
{
    /**
     * @var ResponseFactory
     */
    private $responseFactory;


    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }


    /**
     * @param array<string, mixed> $configuration
     */
    public function create(array $configuration): Container
    {
        if (!isset($configuration['response'])) {
            $configuration['response'] = $this->responseFactory->create();
        }

        return new Container($configuration);
    }
}
