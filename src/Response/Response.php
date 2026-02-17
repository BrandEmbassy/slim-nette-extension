<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Response;

use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Slim\Http\Body;
use stdClass;
use function assert;
use function fopen;
use function is_array;
use function json_encode;
use const JSON_THROW_ON_ERROR;

/**
 * @final
 */
class Response implements ResponseInterface
{
    private PsrResponseInterface $response;


    public function __construct(PsrResponseInterface $response)
    {
        $this->response = $response;
    }


    public function getInnerResponse(): PsrResponseInterface
    {
        return $this->response;
    }


    /**
     * @param mixed[]|stdClass $data
     */
    public function withJson($data, ?int $status = null, int $encodingOptions = 0): static
    {
        $json = json_encode($data, JSON_THROW_ON_ERROR | $encodingOptions);

        $resource = fopen('php://temp', 'r+');
        assert($resource !== false);

        $body = new Body($resource);
        $body->write($json);
        $body->rewind();

        $clone = clone $this;
        $clone->response = $this->response
            ->withHeader('Content-Type', 'application/json;charset=utf-8')
            ->withBody($body);

        if ($status !== null) {
            $clone->response = $clone->response->withStatus($status);
        }

        return $clone;
    }


    /**
     * @param string|UriInterface $url
     */
    public function withRedirect($url, int $statusCode = 302): static
    {
        $clone = clone $this;
        $clone->response = $this->response
            ->withStatus($statusCode)
            ->withHeader('Location', (string)$url);

        return $clone;
    }


    /**
     * @return mixed[]
     *
     * @throws JsonException
     */
    public function getParsedBodyAsArray(): array
    {
        $parsedBody = Json::decode((string)$this->getBody(), Json::FORCE_ARRAY);
        assert(is_array($parsedBody));

        return $parsedBody;
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
