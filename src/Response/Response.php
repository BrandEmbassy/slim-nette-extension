<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Response;

use Psr\Http\Message\StreamInterface;
use Slim\Http\Response as SlimResponse;

/**
 * @final
 */
class Response implements ResponseInterface
{
    private SlimResponse $slimResponse;


    public function __construct(SlimResponse $slimResponse)
    {
        $this->slimResponse = $slimResponse;
    }


    public function getProtocolVersion(): string
    {
        return $this->slimResponse->getProtocolVersion();
    }


    /**
     * @param string $version
     */
    public function withProtocolVersion($version): self
    {
        return new self($this->slimResponse->withProtocolVersion($version));
    }


    /**
     * @return string[][]
     */
    public function getHeaders(): array
    {
        return $this->slimResponse->getHeaders();
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param string $name
     */
    public function hasHeader($name): bool
    {
        return $this->slimResponse->hasHeader($name);
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param string $name
     *
     * @return string[]
     */
    public function getHeader($name): array
    {
        return $this->slimResponse->getHeader($name);
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param string $name
     */
    public function getHeaderLine($name): string
    {
        return $this->slimResponse->getHeaderLine($name);
    }


    /**
     * @param string $name
     * @param string|string[] $value
     */
    public function withHeader($name, $value): self
    {
        return new self($this->slimResponse->withHeader($name, $value));
    }


    /**
     * @param string $name
     * @param string|string[] $value
     */
    public function withAddedHeader($name, $value): self
    {
        return new self($this->slimResponse->withAddedHeader($name, $value));
    }


    /**
     * @param string $name
     */
    public function withoutHeader($name): self
    {
        return new self($this->slimResponse->withoutHeader($name));
    }


    public function getBody(): StreamInterface
    {
        return $this->slimResponse->getBody();
    }


    public function withBody(StreamInterface $body): self
    {
        return new self($this->slimResponse->withBody($body));
    }


    public function getStatusCode(): int
    {
        return $this->slimResponse->getStatusCode();
    }


    /**
     * @param int $code
     * @param string $reasonPhrase
     */
    public function withStatus($code, $reasonPhrase = ''): self
    {
        return new self($this->slimResponse->withStatus($code, $reasonPhrase));
    }


    /**
     * @param mixed[]|object $data
     */
    public function withJson($data, ?int $status = null, int $encodingOptions = 0): self
    {
        return new self($this->slimResponse->withJson($data, $status, $encodingOptions));
    }


    public function getReasonPhrase(): string
    {
        return $this->slimResponse->getReasonPhrase();
    }
}
