<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Response;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

interface ResponseInterface extends PsrResponseInterface
{

    /**
     * @param mixed $data
     * @param int|null $status
     * @param int $encodingOptions
     * @return static
     */
    public function withJson($data, $status = null, $encodingOptions = 0);

}
