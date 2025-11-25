<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Request;

use BrandEmbassy\Slim\MissingApiArgumentException;
use DateTime;
use DateTimeImmutable;
use LogicException;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Slim\Route;
use stdClass;
use function array_key_exists;
use function assert;
use function is_array;
use function is_string;
use function sprintf;

final class Request implements RequestInterface
{
    /**
     * @var stdClass|null
     */
    private $decodedJsonFromBody;

    private ServerRequestInterface $request;


    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }


    /**
     * @param string|int|string[]|int[]|null $default
     *
     * @return string|integer|mixed[]|null
     */
    public function getQueryParam(string $key, $default = null)
    {
        $getParams = $this->getQueryParams();

        return $getParams[$key] ?? $default;
    }


    public function getRequiredArgument(string $name): string
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
     * @param int|string|null $default
     *
     * @return mixed[]|stdClass|string|integer|null
     */
    public function getOptionalField(string $name, $default = null)
    {
        return $this->hasField($name)
            ? $this->getField($name)
            : $default;
    }


    public function hasField(string $name): bool
    {
        return array_key_exists($name, (array)$this->getDecodedJsonFromBody());
    }


    public function getProtocolVersion(): string
    {
        return $this->request->getProtocolVersion();
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param string $version
     */
    public function withProtocolVersion($version): self
    {
        return new self($this->request->withProtocolVersion($version));
    }


    /**
     * @return string[][]
     */
    public function getHeaders(): array
    {
        return $this->request->getHeaders();
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param string $name
     */
    public function hasHeader($name): bool
    {
        return $this->request->hasHeader($name);
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
        return $this->request->getHeader($name);
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param string $name
     */
    public function getHeaderLine($name): string
    {
        return $this->request->getHeaderLine($name);
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param string $name
     * @param string|string[] $value
     */
    public function withHeader($name, $value): self
    {
        return new self($this->request->withHeader($name, $value));
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param string $name
     * @param string|string[] $value
     */
    public function withAddedHeader($name, $value): self
    {
        return new self($this->request->withAddedHeader($name, $value));
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param string $name
     */
    public function withoutHeader($name): self
    {
        return new self($this->request->withoutHeader($name));
    }


    public function getBody(): StreamInterface
    {
        return $this->request->getBody();
    }


    public function withBody(StreamInterface $body): self
    {
        return new self($this->request->withBody($body));
    }


    public function getRequestTarget(): string
    {
        return $this->request->getRequestTarget();
    }


    /**
     * @param mixed $requestTarget
     */
    public function withRequestTarget($requestTarget): self
    {
        return new self($this->request->withRequestTarget($requestTarget));
    }


    public function getMethod(): string
    {
        return $this->request->getMethod();
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param string $method
     */
    public function withMethod($method): self
    {
        return new self($this->request->withMethod($method));
    }


    public function getUri(): UriInterface
    {
        return $this->request->getUri();
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param bool $preserveHost
     */
    public function withUri(UriInterface $uri, $preserveHost = false): self
    {
        return new self($this->request->withUri($uri, $preserveHost));
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
     */
    public function withCookieParams(array $cookies): self
    {
        return new self($this->request->withCookieParams($cookies));
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
     */
    public function withQueryParams(array $query): self
    {
        return new self($this->request->withQueryParams($query));
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
     *
     * @return static
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        return new self($this->request->withUploadedFiles($uploadedFiles));
    }


    /**
     * @return mixed[]|object|null
     */
    public function getParsedBody()
    {
        return $this->request->getParsedBody();
    }


    /**
     * @return mixed[]
     */
    public function getParsedBodyAsArray(): array
    {
        $parsedBody = $this->getParsedBody();
        assert(is_array($parsedBody));

        return $parsedBody;
    }


    /**
     * @param mixed[]|object|null $data
     */
    public function withParsedBody($data): self
    {
        return new self($this->request->withParsedBody($data));
    }


    /**
     * @return mixed[]
     */
    public function getAttributes(): array
    {
        return $this->request->getAttributes();
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param string $name
     * @param mixed $default
     *
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
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param string $name
     * @param mixed $value
     */
    public function withAttribute($name, $value): self
    {
        return new self($this->request->withAttribute($name, $value));
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param string $name
     */
    public function withoutAttribute($name): self
    {
        return new self($this->request->withoutAttribute($name));
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
            throw new LogicException(sprintf("Could not find %s in request's params", $field));
        }

        if (!is_string($datetimeParam)) {
            throw new LogicException(sprintf("Invalid data type %s in request's params", $field));
        }

        $datetime = DateTimeImmutable::createFromFormat(DateTime::ATOM, $datetimeParam);

        if ($datetime === false || $datetime->format(DateTime::ATOM) !== $datetimeParam) {
            throw new LogicException(sprintf('Could not parse %s as datetime', $field));
        }

        return $datetime;
    }
}
