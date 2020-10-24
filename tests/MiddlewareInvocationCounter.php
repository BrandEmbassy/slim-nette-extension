<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim;

use BrandEmbassy\Slim\Response\ResponseInterface;

final class MiddlewareInvocationCounter
{
    /**
     * @var int
     */
    private static $counter = 0;


    public static function invoke(string $headerName, ResponseInterface $response): ResponseInterface
    {
        self::$counter++;

        return $response->withHeader($headerName, 'invoked-' . self::$counter);
    }


    public static function reset(): void
    {
        self::$counter = 0;
    }
}
