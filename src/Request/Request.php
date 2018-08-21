<?php

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
use Slim\Http\Request as SlimRequest;
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

    /**
     * @param ServerRequestInterface $request
     */
    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @inheritdoc
     */
    public function getQueryParam($key, $default = null)
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
    public function getRequiredArgument($name)
    {
        $arguments = $this->request->getAttributes();

        $value = isset($arguments[$name]) ? $arguments[$name] : '';
        $value = Strings::trim($value);

        if ($value === '') {
            throw new MissingApiArgumentException(sprintf('Missing "%s" argument', $name));
        }

        return $value;
    }

    /**
     * @inheritdoc
     * @throws MissingApiArgumentException
     */
    public function getField($name)
    {
        $body = $this->getDecodedJsonFromBody();

        if (!$this->hasField($name)) {
            throw new MissingApiArgumentException(sprintf('Field "%s" is missing in request body', $name));
        }

        return $body->$name;
    }

    /**
     * @inheritdoc
     * @throws MissingApiArgumentException
     */
    public function getOptionalField($name, $default = null)
    {
        return $this->hasField($name) ? $this->getField($name) : $default;
    }

    /**
     * @inheritdoc
     */
    public function hasField($name)
    {
        return array_key_exists($name, (array)$this->getDecodedJsonFromBody());
    }

    /**
     * @inheritDoc
     */
    public function getProtocolVersion()
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
    public function getHeaders()
    {
        return $this->request->getHeaders();
    }

    /**
     * @inheritDoc
     */
    public function hasHeader($name)
    {
        return $this->request->hasHeader($name);
    }

    /**
     * @inheritDoc
     */
    public function getHeader($name)
    {
        return $this->request->getHeader($name);
    }

    /**
     * @inheritDoc
     */
    public function getHeaderLine($name)
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

    /**
     * @inheritDoc
     */
    public function getBody()
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

    /**
     * @inheritDoc
     */
    public function getRequestTarget()
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

    /**
     * @inheritDoc
     */
    public function getMethod()
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

    /**
     * @inheritDoc
     */
    public function getUri()
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
    public function getServerParams()
    {
        return $this->request->getServerParams();
    }

    /**
     * @inheritDoc
     */
    public function getCookieParams()
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
    public function getQueryParams()
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
    public function getUploadedFiles()
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
    public function getAttributes()
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

    /**
     * @inheritdoc
     */
    public function getDateTimeQueryParam($field)
    {
        $datetimeParam = $this->getQueryParam($field);

        if ($datetimeParam === null) {
            throw new LogicException(sprintf('Could not find %s in request\'s params', $field));
        }

        $datetime = DateTimeImmutable::createFromFormat(DateTime::ATOM, $datetimeParam);

        if ($datetime === false || $datetime->format(DateTime::ATOM) !== $datetimeParam) {
            throw new LogicException(sprintf('Could not parse %s as datetime', $field));
        }

        return $datetime;
    }

}
