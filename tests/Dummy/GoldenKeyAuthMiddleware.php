<?php

namespace BrandEmbassyTest\Slim\Dummy;

use BrandEmbassy\Slim\Middleware;
use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;
use Exception;

final class GoldenKeyAuthMiddleware implements Middleware
{

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $headerData = $request->getHeader('goldenKey');
        $token = reset($headerData);
        $token = $token !== false ? $token : '';

        if ($token !== 'uber-secret-token-made-of-pure-gold') {
            return $response->withJson(['error' => 'YOU SHALL NOT PASS!'], 401);
        }

        return $next($request, $response);
    }
}
