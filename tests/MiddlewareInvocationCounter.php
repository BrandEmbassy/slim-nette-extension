<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim;

/**
 * @final
 */
class MiddlewareInvocationCounter
{
    private static int $counter = 0;


    public static function getNextValue(): string
    {
        return 'invoked-' . self::$counter++;
    }


    public static function reset(): void
    {
        self::$counter = 0;
    }
}
