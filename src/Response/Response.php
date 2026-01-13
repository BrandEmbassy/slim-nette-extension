<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Response;

use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Psr\Http\Message\UriInterface;
use Slim\Psr7\Response as SlimResponse;
use stdClass;
use function assert;
use function is_array;

/**
 * @final
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
     * @return static
     */
    public function withJson($data, ?int $status = null, int $encodingOptions = 0)
    {
        $response = clone $this;
        $response->getBody()->write(Json::encode($data, $encodingOptions));
        $response = $response->withHeader('Content-Type', 'application/json');
        
        if ($status !== null) {
            $response = $response->withStatus($status);
        }

        return $response;
    }

    /**
     * @param string|UriInterface $url
     *
     * @return static
     */
    public function withRedirect($url, int $statusCode = 302)
    {
        $response = $this->withHeader('Location', (string)$url);
        
        if ($statusCode !== $this->getStatusCode()) {
            $response = $response->withStatus($statusCode);
        }

        return $response;
    }
}
