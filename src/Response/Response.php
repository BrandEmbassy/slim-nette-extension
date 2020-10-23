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
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param string $version
     *
     * @return static
     */
    public function withProtocolVersion($version): self
    {
        return new static($this->slimResponse->withProtocolVersion($version));
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
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param string $name
     * @param string|string[] $value
     *
     * @return static
     */
    public function withHeader($name, $value): self
    {
        return new static($this->slimResponse->withHeader($name, $value));
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param string $name
     * @param string|string[] $value
     *
     * @return static
     */
    public function withAddedHeader($name, $value): self
    {
        return new static($this->slimResponse->withAddedHeader($name, $value));
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param string $name
     *
     * @return static
     */
    public function withoutHeader($name): self
    {
        return new static($this->slimResponse->withoutHeader($name));
    }


    public function getBody(): StreamInterface
    {
        return $this->slimResponse->getBody();
    }


    /**
     * @return static
     */
    public function withBody(StreamInterface $body): self
    {
        return new static($this->slimResponse->withBody($body));
    }


    public function getStatusCode(): int
    {
        return $this->slimResponse->getStatusCode();
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param int $code
     * @param string $reasonPhrase
     *
     * @return static
     */
    public function withStatus($code, $reasonPhrase = ''): self
    {
        return new static($this->slimResponse->withStatus($code, $reasonPhrase));
    }


    /**
     * @param mixed[]|object $data
     *
     * @return static
     */
    public function withJson($data, ?int $status = null, int $encodingOptions = 0): self
    {
        return new static($this->slimResponse->withJson($data, $status, $encodingOptions));
    }


    public function getReasonPhrase(): string
    {
        return $this->slimResponse->getReasonPhrase();
    }
}
