<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Response;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @final
 *
 * Thin decorator that implements ResponseInterface by delegating all
 * Psr\Http\Message\ResponseInterface methods to the wrapped PSR-7 response.
 * This allows Slim 4's native PSR-7 objects to satisfy the legacy
 * ResponseInterface type hint without any behavioural changes.
 */
class ResponseDecorator implements ResponseInterface
{
    public function __construct(
        private readonly PsrResponseInterface $inner
    ) {
    }


    public function getStatusCode(): int
    {
        return $this->inner->getStatusCode();
    }


    public function withStatus(int $code, string $reasonPhrase = ''): static
    {
        return new self($this->inner->withStatus($code, $reasonPhrase));
    }


    public function getReasonPhrase(): string
    {
        return $this->inner->getReasonPhrase();
    }


    public function getProtocolVersion(): string
    {
        return $this->inner->getProtocolVersion();
    }


    public function withProtocolVersion(string $version): static
    {
        return new self($this->inner->withProtocolVersion($version));
    }


    /**
     * @return array<string, array<string>>
     */
    public function getHeaders(): array
    {
        return $this->inner->getHeaders();
    }


    public function hasHeader(string $name): bool
    {
        return $this->inner->hasHeader($name);
    }


    /**
     * @return array<string>
     */
    public function getHeader(string $name): array
    {
        return $this->inner->getHeader($name);
    }


    public function getHeaderLine(string $name): string
    {
        return $this->inner->getHeaderLine($name);
    }


    /**
     * @param string|array<string> $value
     */
    public function withHeader(string $name, $value): static
    {
        return new self($this->inner->withHeader($name, $value));
    }


    /**
     * @param string|array<string> $value
     */
    public function withAddedHeader(string $name, $value): static
    {
        return new self($this->inner->withAddedHeader($name, $value));
    }


    public function withoutHeader(string $name): static
    {
        return new self($this->inner->withoutHeader($name));
    }


    public function getBody(): StreamInterface
    {
        return $this->inner->getBody();
    }


    public function withBody(StreamInterface $body): static
    {
        return new self($this->inner->withBody($body));
    }
}
