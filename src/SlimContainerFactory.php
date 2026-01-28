<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim;

use BrandEmbassy\Slim\Request\RequestFactory;
use BrandEmbassy\Slim\Response\ResponseFactory;

/**
 * @final
 *
 * This class is kept for compatibility but is not used in Slim 4
 * as Slim 4 doesn't use its own container.
 */
class SlimContainerFactory
{
    private ResponseFactory $responseFactory;

    private RequestFactory $requestFactory;


    public function __construct(
        ResponseFactory $responseFactory,
        RequestFactory $requestFactory
    ) {
        $this->responseFactory = $responseFactory;
        $this->requestFactory = $requestFactory;
    }


    /**
     * @param array<string, mixed> $configuration
     *
     * @return array<string, mixed>
     */
    public function create(array $configuration): array
    {
        if (!isset($configuration['response'])) {
            $configuration['response'] = $this->responseFactory->create();
        }

        if (!isset($configuration['request'])) {
            $configuration['request'] = $this->requestFactory->create();
        }

        return $configuration;
    }
}
