<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Tools;

use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\Assert;

/**
 * @final
 */
class DateTimeAssertions
{
    public static function assertDateTimeTimestampsEquals(
        DateTimeImmutable $expectedDateTimeImmutable,
        DateTimeImmutable $dateTimeImmutable
    ): void {
        Assert::assertSame($expectedDateTimeImmutable->getTimestamp(), $dateTimeImmutable->getTimestamp());
    }


    public static function assertDateTimeTimestampEqualsDateTime(
        int $expectedTimestamp,
        DateTimeImmutable $dateTime
    ): void {
        Assert::assertSame($expectedTimestamp, $dateTime->getTimestamp());
    }


    public static function assertDateTimeAtomEqualsDateTime(
        string $expectedDateTimeInAtom,
        DateTimeImmutable $dateTime
    ): void {
        Assert::assertSame($expectedDateTimeInAtom, $dateTime->format(DateTime::ATOM));
    }
}