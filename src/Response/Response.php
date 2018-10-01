<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Response;

use Psr\Http\Message\StreamInterface;
use Slim\Http\Response as SlimResponse;

final class Response implements ResponseInterface
{

    /**
     * @var SlimResponse
     */
    private $slimResponse;

    public function __construct(SlimResponse $slimResponse)
    {
        $this->slimResponse = $slimResponse;
    }

    public function getProtocolVersion(): string
    {
        return $this->slimResponse->getProtocolVersion();
    }

    /**
     * @inheritdoc
     */
    public function withProtocolVersion($version)
    {
        return new static($this->slimResponse->withProtocolVersion($version));
    }

    /**
     * @inheritdoc
     */
    public function getHeaders(): array
    {
        return $this->slimResponse->getHeaders();
    }

    /**
     * @inheritdoc
     */
    public function hasHeader($name): bool
    {
        return $this->slimResponse->hasHeader($name);
    }

    /**
     * @inheritdoc
     */
    public function getHeader($name): array
    {
        return $this->slimResponse->getHeader($name);
    }

    /**
     * @inheritdoc
     */
    public function getHeaderLine($name): string
    {
        return $this->slimResponse->getHeaderLine($name);
    }

    /**
     * @inheritdoc
     */
    public function withHeader($name, $value)
    {
        return new static($this->slimResponse->withHeader($name, $value));
    }

    /**
     * @inheritdoc
     */
    public function withAddedHeader($name, $value)
    {
        return new static($this->slimResponse->withAddedHeader($name, $value));
    }

    /**
     * @inheritdoc
     */
    public function withoutHeader($name)
    {
        return new static($this->slimResponse->withoutHeader($name));
    }

    public function getBody(): StreamInterface
    {
        return $this->slimResponse->getBody();
    }

    /**
     * @inheritdoc
     */
    public function withBody(StreamInterface $body)
    {
        return new static($this->slimResponse->withBody($body));
    }

    public function getStatusCode(): int
    {
        return $this->slimResponse->getStatusCode();
    }

    /**
     * @inheritdoc
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        return new static($this->slimResponse->withStatus($code, $reasonPhrase));
    }

    /**
     * @inheritdoc
     */
    public function withJson($data, $status = null, $encodingOptions = 0)
    {
        return new static($this->slimResponse->withJson($data, $status, $encodingOptions));
    }

    public function getReasonPhrase(): string
    {
        return $this->slimResponse->getReasonPhrase();
    }

}
