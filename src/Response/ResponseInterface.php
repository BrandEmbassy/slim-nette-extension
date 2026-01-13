<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Response;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\UriInterface;
use stdClass;

interface ResponseInterface extends PsrResponseInterface
{
    public function getInnerResponse(): PsrResponseInterface;


    /**
     * @param mixed[]|stdClass $data
     *
     * @return static
     */
    public function withJson($data, ?int $status = null, int $encodingOptions = 0);


    /**
     * @param string|UriInterface $url
     *
     * @return static
     */
    public function withRedirect($url, int $statusCode = 302);


    /**
     * @return mixed[]
     */
    public function getParsedBodyAsArray(): array;
}
