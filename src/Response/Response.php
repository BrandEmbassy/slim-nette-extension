<?php

namespace BrandEmbassy\Slim\Response;

use Psr\Http\Message\StreamInterface;
use Slim\Http\Response as SlimResponse;

final class Response implements ResponseInterface
{

    /**
     * @var SlimResponse
     */
    private $slimResponse;

    /**
     * @param SlimResponse $slimResponse
     */
    public function __construct(SlimResponse $slimResponse)
    {
        $this->slimResponse = $slimResponse;
    }

    /**
     * @inheritdoc
     */
    public function getProtocolVersion()
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
    public function getHeaders()
    {
        return $this->slimResponse->getHeaders();
    }

    /**
     * @inheritdoc
     */
    public function hasHeader($name)
    {
        return $this->slimResponse->hasHeader($name);
    }

    /**
     * @inheritdoc
     */
    public function getHeader($name)
    {
        return $this->slimResponse->getHeader($name);
    }

    /**
     * @inheritdoc
     */
    public function getHeaderLine($name)
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

    /**
     * @inheritdoc
     */
    public function getBody()
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

    /**
     * @inheritdoc
     */
    public function getStatusCode()
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

    /**
     * @inheritdoc
     */
    public function getReasonPhrase()
    {
        return $this->slimResponse->getReasonPhrase();
    }

}
