<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Response;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @final
 *
 * Wrapper around PSR-7 ResponseInterface providing convenience methods
 * for common response operations.
 */
class Response implements ResponseInterface
{
    private PsrResponseInterface $response;


    public function __construct(PsrResponseInterface $response)
    {
        $this->response = $response;
    }


    /**
     * Get the inner PSR-7 response
     */
    public function getInnerResponse(): PsrResponseInterface
    {
        return $this->response;
    }


    public function getProtocolVersion(): string
    {
        return $this->response->getProtocolVersion();
    }


    public function withProtocolVersion(string $version): static
    {
        $clone = clone $this;
        $clone->response = $this->response->withProtocolVersion($version);

        return $clone;
    }


    /**
     * @return string[][]
     */
    public function getHeaders(): array
    {
        return $this->response->getHeaders();
    }


    public function hasHeader(string $name): bool
    {
        return $this->response->hasHeader($name);
    }


    /**
     * @return string[]
     */
    public function getHeader(string $name): array
    {
        return $this->response->getHeader($name);
    }


    public function getHeaderLine(string $name): string
    {
        return $this->response->getHeaderLine($name);
    }


    /**
     * @param string|string[] $value
     */
    public function withHeader(string $name, $value): static
    {
        $clone = clone $this;
        $clone->response = $this->response->withHeader($name, $value);

        return $clone;
    }


    /**
     * @param string|string[] $value
     */
    public function withAddedHeader(string $name, $value): static
    {
        $clone = clone $this;
        $clone->response = $this->response->withAddedHeader($name, $value);

        return $clone;
    }


    public function withoutHeader(string $name): static
    {
        $clone = clone $this;
        $clone->response = $this->response->withoutHeader($name);

        return $clone;
    }


    public function getBody(): StreamInterface
    {
        return $this->response->getBody();
    }


    public function withBody(StreamInterface $body): static
    {
        $clone = clone $this;
        $clone->response = $this->response->withBody($body);

        return $clone;
    }


    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }


    public function withStatus(int $code, string $reasonPhrase = ''): static
    {
        $clone = clone $this;
        $clone->response = $this->response->withStatus($code, $reasonPhrase);

        return $clone;
    }


    public function getReasonPhrase(): string
    {
        return $this->response->getReasonPhrase();
    }
}
