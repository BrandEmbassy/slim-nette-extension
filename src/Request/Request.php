<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Request;

use BrandEmbassy\Slim\MissingApiArgumentException;
use Closure;
use DateTime;
use DateTimeImmutable;
use LogicException;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use RuntimeException;
use Slim\Http\RequestBody;
use Slim\Route;
use stdClass;
use function array_key_exists;
use function is_string;
use function sprintf;

final class Request implements RequestInterface
{
    /**
     * @var stdClass|null
     */
    private $decodedJsonFromBody;

    /**
     * @var ServerRequestInterface
     */
    private $request;

    /**
     * List of request body encoders (e.g., url-encoded, JSON, XML, multipart)
     *
     * @var callable[]
     */
    private $bodyEncoders = [];

    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;

        $this->registerMediaTypeEncoder(
            'application/json',
            static function ($data): ?string {
                return Json::encode($data);
            }
        );
    }

    /**
     * @param string $key
     * @param string|int|null $default
     * @return string|integer|mixed[]|null
     */
    public function getQueryParam(string $key, $default = null)
    {
        $getParams = $this->getQueryParams();
        $result = $default;

        if (isset($getParams[$key])) {
            $result = $getParams[$key];
        }

        return $result;
    }


    /**
     * @param string $name
     * @return string|integer
     */
    public function getRequiredArgument(string $name)
    {
        $arguments = $this->request->getAttributes();

        $value = $arguments[$name] ?? '';
        $value = Strings::trim($value);

        if ($value === '') {
            throw new MissingApiArgumentException(sprintf('Missing "%s" argument', $name));
        }

        return $value;
    }


    /**
     * @param string $name
     * @return mixed[]|stdClass|string
     */
    public function getField(string $name)
    {
        $body = $this->getDecodedJsonFromBody();

        if (!$this->hasField($name)) {
            throw new MissingApiArgumentException(sprintf('Field "%s" is missing in request body', $name));
        }

        return ((array)$body)[$name];
    }


    /**
     * @param string $name
     * @param int|string|null $default
     * @return mixed[]|stdClass|string|integer|null
     */
    public function getOptionalField($name, $default = null)
    {
        return $this->hasField($name)
            ? $this->getField($name)
            : $default;
    }


    /**
     * @param string $name
     * @return boolean
     */
    public function hasField($name): bool
    {
        return array_key_exists($name, (array)$this->getDecodedJsonFromBody());
    }


    public function getProtocolVersion(): string
    {
        return $this->request->getProtocolVersion();
    }


    /**
     * @param string $version
     * @return static
     */
    public function withProtocolVersion($version): self
    {
        return new static($this->request->withProtocolVersion($version));
    }


    /**
     * @return string[][]
     */
    public function getHeaders(): array
    {
        return $this->request->getHeaders();
    }


    /**
     * @param string $name
     * @return boolean
     */
    public function hasHeader($name): bool
    {
        return $this->request->hasHeader($name);
    }


    /**
     * @param string $name
     * @return string[]
     */
    public function getHeader($name): array
    {
        return $this->request->getHeader($name);
    }


    /**
     * @param string $name
     * @return string
     */
    public function getHeaderLine($name): string
    {
        return $this->request->getHeaderLine($name);
    }


    /**
     * @param string $name
     * @param string|string[] $value
     * @return static
     */
    public function withHeader($name, $value): self
    {
        return new static($this->request->withHeader($name, $value));
    }


    /**
     * @param string $name
     * @param string|string[] $value
     * @return static
     */
    public function withAddedHeader($name, $value): self
    {
        return new static($this->request->withAddedHeader($name, $value));
    }


    /**
     * @param string $name
     * @return static
     */
    public function withoutHeader($name): self
    {
        return new static($this->request->withoutHeader($name));
    }


    public function getBody(): StreamInterface
    {
        return $this->request->getBody();
    }


    /**
     * @param StreamInterface $body
     * @return static
     */
    public function withBody(StreamInterface $body): self
    {
        return new static($this->request->withBody($body));
    }


    public function getRequestTarget(): string
    {
        return $this->request->getRequestTarget();
    }


    /**
     * @param mixed $requestTarget
     * @return static
     */
    public function withRequestTarget($requestTarget): self
    {
        return new static($this->request->withRequestTarget($requestTarget));
    }


    public function getMethod(): string
    {
        return $this->request->getMethod();
    }


    /**
     * @param string $method
     * @return static
     */
    public function withMethod($method): self
    {
        return new static($this->request->withMethod($method));
    }


    public function getUri(): UriInterface
    {
        return $this->request->getUri();
    }


    /**
     * @param UriInterface $uri
     * @param bool $preserveHost
     * @return static
     */
    public function withUri(UriInterface $uri, $preserveHost = false): self
    {
        return new static($this->request->withUri($uri, $preserveHost));
    }


    /**
     * @return mixed[]
     */
    public function getServerParams(): array
    {
        return $this->request->getServerParams();
    }


    /**
     * @return mixed[]
     */
    public function getCookieParams(): array
    {
        return $this->request->getCookieParams();
    }


    /**
     * @param mixed[] $cookies
     * @return static
     */
    public function withCookieParams(array $cookies): self
    {
        return new static($this->request->withCookieParams($cookies));
    }


    /**
     * @return mixed[]
     */
    public function getQueryParams(): array
    {
        return $this->request->getQueryParams();
    }


    /**
     * @param mixed[] $query
     * @return static
     */
    public function withQueryParams(array $query): self
    {
        return new static($this->request->withQueryParams($query));
    }


    /**
     * @return mixed[]
     */
    public function getUploadedFiles(): array
    {
        return $this->request->getUploadedFiles();
    }


    /**
     * @param mixed[] $uploadedFiles
     * @return static
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        return new static($this->request->withUploadedFiles($uploadedFiles));
    }


    /**
     * @return mixed[]|object|null
     */
    public function getParsedBody()
    {
        return $this->request->getParsedBody();
    }


    /**
     * @param mixed[]|object|null $data
     * @return static
     */
    public function withParsedBody($data): self
    {
        $mediaType = $this->getMediaType();

        // Check if this specific media type has a encoder registered first
        if (!isset($this->bodyEncoders[$mediaType])) {
            // If not, look for a media type with a structured syntax suffix (RFC 6839)
            $parts = explode('+', $mediaType);
            if (count($parts) >= 2) {
                $mediaType = 'application/' . $parts[count($parts)-1];
            }
        }

        if (isset($this->bodyEncoders[$mediaType])) {
            $encoded = $this->bodyEncoders[$mediaType]($data);

            if (!is_string($encoded)) {
                throw new RuntimeException(
                    'Request body media type encoder return value must be a string'
                );
            }
        } else {
            throw new RuntimeException(
                \sprintf(
                    'Parsed body could not be set because there is no encoder for media type \'%s\'',
                    $mediaType
                )
            );
        }

        $requestBody = new RequestBody();
        $requestBody->write($encoded);
        $requestBody->rewind();

        return new static($this->request->withBody($requestBody)->withParsedBody($data));
    }


    /**
     * @return mixed[]
     */
    public function getAttributes(): array
    {
        return $this->request->getAttributes();
    }


    /**
     * @param string $name
     * @param mixed  $default
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        $value = $this->request->getAttribute($name, $default);

        if ($value === $default) {
            $route = $this->request->getAttribute('route', $default);

            if ($route instanceof Route) {
                $value = $route->getArgument($name, $default);
            }
        }

        return $value;
    }


    /**
     * @param string $name
     * @param mixed $value
     * @return static
     */
    public function withAttribute($name, $value): self
    {
        return new static($this->request->withAttribute($name, $value));
    }


    /**
     * @param string $name
     * @return static
     */
    public function withoutAttribute($name): self
    {
        return new static($this->request->withoutAttribute($name));
    }


    /**
     * @return mixed[]|stdClass
     */
    public function getDecodedJsonFromBody()
    {
        if ($this->decodedJsonFromBody === null) {
            $contents = (string)$this->request->getBody();
            $this->decodedJsonFromBody = Json::decode($contents);
        }

        return $this->decodedJsonFromBody;
    }


    public function getDateTimeQueryParam(string $field): DateTimeImmutable
    {
        $datetimeParam = $this->getQueryParam($field);

        if ($datetimeParam === null) {
            throw new LogicException(sprintf('Could not find %s in request\'s params', $field));
        }

        if (!is_string($datetimeParam)) {
            throw new LogicException(sprintf('Invalid data type %s in request\'s params', $field));
        }

        $datetime = DateTimeImmutable::createFromFormat(DateTime::ATOM, $datetimeParam);

        if ($datetime === false || $datetime->format(DateTime::ATOM) !== $datetimeParam) {
            throw new LogicException(sprintf('Could not parse %s as datetime', $field));
        }

        return $datetime;
    }

    /**
     * Register media type encoder.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param string $mediaType A HTTP media type (excluding content-type params).
     * @param callable $callable A callable that returns encoded content for  media type.
     */
    public function registerMediaTypeEncoder(string $mediaType, callable $callable): void
    {
        if ($callable instanceof Closure) {
            $callable = $callable->bindTo($this);
        }

        $this->bodyEncoders[$mediaType] = $callable;
    }

    /**
     * Get request content type.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return string|null The request content type, if known
     */
    public function getContentType(): ?string
    {
        $result = $this->getHeader('Content-Type');

        return $result ? $result[0] : null;
    }

    /**
     * Get request media type, if known.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return string|null The request media type, minus content-type params
     */
    public function getMediaType(): ?string
    {
        $contentType = $this->getContentType();
        if ($contentType) {
            $contentTypeParts = preg_split('/\s*[;,]\s*/', $contentType);

            return strtolower($contentTypeParts[0]);
        }

        return null;
    }

}
