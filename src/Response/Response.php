<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Response;

use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\StreamInterface;
use Slim\Http\Headers;
use Slim\Http\Response as SlimResponse;
use Slim\Http\StatusCode;
use Slim\Interfaces\Http\HeadersInterface;
use function assert;
use function implode;
use function is_array;

/**
 * @final
 */
class Response extends SlimResponse implements ResponseInterface
{
    /**
     * Creates a new Response instance.
     *
     * Supports two construction modes:
     * - Legacy (Slim 3): new Response(200, $headers, $body)
     * - PSR-7 wrapping:  new Response($psrResponse)
     *
     * The PSR-7 wrapping mode enables gradual migration to Slim 4 by allowing
     * callers to construct Response from any PSR-7 response instance.
     */
    public function __construct(
        PsrResponseInterface|int $statusOrResponse = StatusCode::HTTP_OK,
        ?HeadersInterface $headers = null,
        ?StreamInterface $body = null,
    ) {
        if ($statusOrResponse instanceof PsrResponseInterface) {
            parent::__construct(
                $statusOrResponse->getStatusCode(),
                new Headers($this->flattenHeaders($statusOrResponse->getHeaders())),
                $statusOrResponse->getBody(),
            );

            return;
        }

        parent::__construct($statusOrResponse, $headers, $body);
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


    public function getInnerResponse(): PsrResponseInterface
    {
        return $this;
    }


    /**
     * Flattens PSR-7 headers (string[][]) to string[] for Slim 3 Headers constructor.
     *
     * @param string[][] $headers
     *
     * @return string[]
     */
    private function flattenHeaders(array $headers): array
    {
        $flattened = [];

        foreach ($headers as $name => $values) {
            $flattened[$name] = implode(', ', $values);
        }

        return $flattened;
    }
}
