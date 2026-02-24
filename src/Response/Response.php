<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Response;

use JsonException;
use Nette\Utils\Json;
use Psr\Http\Message\UriInterface;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Response as SlimResponse;
use stdClass;
use function assert;
use function is_array;
use function json_encode;
use const JSON_THROW_ON_ERROR;

/**
 * @final
 *
 * Extends Slim 4's PSR-7 Response with convenience methods.
 * All PSR-7 methods are inherited from the parent.
 */
class Response extends SlimResponse implements ResponseInterface
{
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


    /**
     * @param mixed[]|stdClass $data
     *
     * @throws JsonException
     */
    public function withJson($data, ?int $status = null, int $encodingOptions = 0): static
    {
        $json = json_encode($data, $encodingOptions | JSON_THROW_ON_ERROR);

        $streamFactory = new StreamFactory();
        $body = $streamFactory->createStream($json);

        $response = $this
            ->withBody($body)
            ->withHeader('Content-Type', 'application/json;charset=utf-8');

        if ($status !== null) {
            return $response->withStatus($status);
        }

        return $response;
    }


    /**
     * @param string|UriInterface $url
     */
    public function withRedirect($url, int $statusCode = 302): static
    {
        return $this
            ->withHeader('Location', (string)$url)
            ->withStatus($statusCode);
    }
}
