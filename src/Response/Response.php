<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Response;

use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Psr\Http\Message\StreamInterface;
use Slim\Http\Headers;
use Slim\Http\Response as SlimResponse;
use Slim\Interfaces\Http\HeadersInterface;
use function assert;
use function is_array;

/**
 * @final
 */
class Response extends SlimResponse implements ResponseInterface
{
    /**
     * @phpstan-param HeadersInterface<string, string|string[]>|null $headers
     */
    public function __construct(int | SlimResponse $status = 200, ?HeadersInterface $headers = null, ?StreamInterface $body = null)
    {
        if ($status instanceof SlimResponse) {
            parent::__construct($status->getStatusCode(), new Headers($status->getHeaders()), $status->getBody());

            return;
        }

        parent::__construct($status, $headers, $body);
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
}
