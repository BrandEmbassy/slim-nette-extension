<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Response;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\StreamInterface;
use Slim\Psr7\Response as SlimResponse;

/**
 * @final
 *
 * Wraps a PSR-7 Response and implements ResponseInterface.
 *
 * Uses composition instead of inheritance to avoid coupling to Slim's
 * internal PSR-7 implementation.
 */
class Response implements ResponseInterface
{
    private readonly PsrResponseInterface $inner;


    /**
     * @param PsrResponseInterface|int $statusOrResponse PSR-7 response to wrap, or HTTP status code for a new response
     */
    public function __construct(PsrResponseInterface|int $statusOrResponse = 200)
    {
        $this->inner = $statusOrResponse instanceof PsrResponseInterface
            ? $statusOrResponse
            : new SlimResponse($statusOrResponse);
    }


    public function getStatusCode(): int
    {
        return $this->inner->getStatusCode();
    }


    public function getReasonPhrase(): string
    {
        return $this->inner->getReasonPhrase();
    }


    public function withStatus(int $code, string $reasonPhrase = ''): static
    {
        return new self($this->inner->withStatus($code, $reasonPhrase));
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
