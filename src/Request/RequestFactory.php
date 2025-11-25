<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim\Request;

interface RequestFactory
{
    public function create(): RequestInterface;
}
