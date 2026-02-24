<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Response;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\UriInterface;
use Slim\Http\StatusCode;
use stdClass;

interface ResponseInterface extends PsrResponseInterface
{
    /**
     * @deprecated Use JsonResponse::from() instead
     *
     * @param mixed[]|stdClass $data
     *
     * @return static
     */
    public function withJson($data, ?int $status = null, int $encodingOptions = 0);


    /**
     * @deprecated Use PSR-7 $response->withHeader('Location', $url)->withStatus($statusCode) instead
     *
     * @param string|UriInterface $url
     *
     * @return static
     */
    public function withRedirect($url, int $statusCode = StatusCode::HTTP_FOUND);


    /**
     * @deprecated Will be removed in v6. Decode the response body directly.
     *
     * @return mixed[]
     */
    public function getParsedBodyAsArray(): array;
}
