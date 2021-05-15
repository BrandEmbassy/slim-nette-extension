<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Response;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Slim\Http\StatusCode;
use stdClass;

interface ResponseInterface extends PsrResponseInterface
{
    /**
     * @param mixed[]|stdClass $data
     *
     * @return static
     */
    public function withJson($data, ?int $status = null, int $encodingOptions = 0);


    public function withRedirect(string $url, int $statusCode = StatusCode::HTTP_FOUND);


    public function getParsedBodyAsArray(): array;
}
