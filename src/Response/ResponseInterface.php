<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Response;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

interface ResponseInterface extends PsrResponseInterface
{
    /**
     * Get the inner PSR-7 ResponseInterface
     */
    public function getInnerResponse(): PsrResponseInterface;
}
