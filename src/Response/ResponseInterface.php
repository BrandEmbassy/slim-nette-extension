<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Response;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

interface ResponseInterface extends PsrResponseInterface
{
    /**
     * @param mixed[] $data
     *
     * @return static
     */
    public function withJson(array $data, ?int $status = null, int $encodingOptions = 0);
}
