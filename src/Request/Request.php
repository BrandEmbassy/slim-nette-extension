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

    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     * @throws MissingApiArgumentException
     */
    public function getRequiredArgument(string $name): string
    {
        $arguments = $this->request->getAttributes();

        $value = $arguments[$name] ?? '';
        $value = Strings::trim($value);

        if ($value === '') {
            throw new MissingApiArgumentException(\sprintf('Missing "%s" argument', $name));
        }

        return $value;
    }

    /**
     * @inheritdoc
     * @throws MissingApiArgumentException
     */
    public function getField(string $name)
    {
        $body = $this->getDecodedJsonFromBody();

        if (!$this->hasField($name)) {
            throw new MissingApiArgumentException(\sprintf('Field "%s" is missing in request body', $name));
        }

        return $body->$name;
    }

    /**
     * @inheritdoc
     * @throws MissingApiArgumentException
     */
    public function getOptionalField($name, $default = null)
    {
        return $this->hasField($name)
            ? $this->getField($name)
            : $default;
    }

    /**
     * @inheritdoc
     */
    public function hasField($name): bool
    {
        return \array_key_exists($name, (array)$this->getDecodedJsonFromBody());
    }

    public function getProtocolVersion(): string
    {
        return $this->request->getProtocolVersion();
    }

    /**
     * @inheritDoc
     */
    public function withProtocolVersion($version)
    {
        return new static($this->request->withProtocolVersion($version));
    }

    /**
     * @inheritDoc
     */
    public function getHeaders(): array
    {
        return $this->request->getHeaders();
    }

    /**
     * @inheritDoc
     */
    public function hasHeader($name): bool
    {
        return $this->request->hasHeader($name);
    }

    /**
     * @inheritDoc
     */
    public function getHeader($name): array
    {
        return $this->request->getHeader($name);
    }

    /**
     * @inheritDoc
     */
    public function getHeaderLine($name): string
    {
        return $this->request->getHeaderLine($name);
    }

    /**
     * @inheritDoc
     */
    public function withHeader($name, $value)
    {
        return new static($this->request->withHeader($name, $value));
    }

    /**
     * @inheritDoc
     */
    public function withAddedHeader($name, $value)
    {
        return new static($this->request->withAddedHeader($name, $value));
    }

    /**
     * @inheritDoc
     */
    public function withoutHeader($name)
    {
        return new static($this->request->withoutHeader($name));
    }

    public function getBody(): StreamInterface
    {
        return $this->request->getBody();
    }

    /**
     * @inheritDoc
     */
    public function withBody(StreamInterface $body)
    {
        return new static($this->request->withBody($body));
    }

    public function getRequestTarget(): string
    {
        return $this->request->getRequestTarget();
    }

    /**
     * @inheritDoc
     */
    public function withRequestTarget($requestTarget)
    {
        return new static($this->request->withRequestTarget($requestTarget));
    }

    public function getMethod(): string
    {
        return $this->request->getMethod();
    }

    /**
     * @inheritDoc
     */
    public function withMethod($method)
    {
        return new static($this->request->withMethod($method));
    }

    public function getUri(): UriInterface
    {
        return $this->request->getUri();
    }

    /**
     * @inheritDoc
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        return new static($this->request->withUri($uri, $preserveHost));
    }

    /**
     * @inheritDoc
     */
    public function getServerParams(): array
    {
        return $this->request->getServerParams();
    }

    /**
     * @inheritDoc
     */
    public function getCookieParams(): array
    {
        return $this->request->getCookieParams();
    }

    /**
     * @inheritDoc
     */
    public function withCookieParams(array $cookies)
    {
        return new static($this->request->withCookieParams($cookies));
    }

    /**
     * @inheritDoc
     */
    public function getQueryParams(): array
    {
        return $this->request->getQueryParams();
    }

    /**
     * @inheritDoc
     */
    public function withQueryParams(array $query)
    {
        return new static($this->request->withQueryParams($query));
    }

    /**
     * @inheritDoc
     */
    public function getUploadedFiles(): array
    {
        return $this->request->getUploadedFiles();
    }

    /**
     * @inheritDoc
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        return new static($this->request->withUploadedFiles($uploadedFiles));
    }

    /**
     * @inheritDoc
     */
    public function getParsedBody()
    {
        return $this->request->getParsedBody();
    }

    /**
     * @inheritDoc
     */
    public function withParsedBody($data)
    {
        return new static($this->request->withParsedBody($data));
    }

    /**
     * @inheritDoc
     */
    public function getAttributes(): array
    {
        return $this->request->getAttributes();
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
     */
    public function withAttribute($name, $value)
    {
        return new static($this->request->withAttribute($name, $value));
    }

    /**
     * @inheritDoc
     */
    public function withoutAttribute($name)
    {
        return new static($this->request->withoutAttribute($name));
    }

    /**
     * @inheritdoc
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
            throw new LogicException(\sprintf('Could not find %s in request\'s params', $field));
        }

        $datetime = DateTimeImmutable::createFromFormat(DateTime::ATOM, $datetimeParam);

        if ($datetime === false || $datetime->format(DateTime::ATOM) !== $datetimeParam) {
            throw new LogicException(\sprintf('Could not parse %s as datetime', $field));
        }

        return $datetime;
    }

}
