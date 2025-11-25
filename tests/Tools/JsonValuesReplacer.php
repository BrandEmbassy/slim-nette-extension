<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Tools;

use Nette\StaticClass;
use Nette\Utils\Json;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function sprintf;
use function str_replace;

/**
 * @final
 */
class JsonValuesReplacer
{
    use StaticClass;


    /**
     * @param array<string, int|float|bool|string|null|array<mixed>> $valuesToReplace
     */
    public static function replace(array $valuesToReplace, string $jsonString): string
    {
        /** @var string[] $keys */
        $keys = [];
        /** @var string[] $values */
        $values = [];
        foreach ($valuesToReplace as $key => $value) {
            foreach (self::getReplacementPairs($key, $value) as $replacementPair) {
                $keys[] = $replacementPair->key;
                $values[] = $replacementPair->value;
            }
        }

        return str_replace($keys, $values, $jsonString);
    }


    /**
     * @return ReplacementPair[]
     */
    private static function getReplacementPairs(string $key, mixed $value): array
    {
        $replacementPairs = [];
        if (is_array($value)) {
            $replacementPairs[] = new ReplacementPair(
                sprintf('"%s"', self::decorateReplacementKey($key)),
                Json::encode($value)
            );

            return $replacementPairs;
        }

        $replacementPairs[] = new ReplacementPair(
            '%%' . $key . '%%',
            (string)$value
        );

        if (is_int($value) || is_float($value)) {
            $replacementPairs[] = new ReplacementPair(
                self::decorateReplacementKey($key, 'string'),
                (string)$value
            );
            $replacementPairs[] = new ReplacementPair(
                sprintf('"%s"', self::decorateReplacementKey($key)),
                (string)$value
            );

            return $replacementPairs;
        }

        if (is_bool($value)) {
            $replacementPairs[] = new ReplacementPair(
                sprintf('"%s"', self::decorateReplacementKey($key)),
                $value ? 'true' : 'false'
            );

            return $replacementPairs;
        }

        $replacementPairs[] = new ReplacementPair(
            sprintf('"%s"', self::decorateReplacementKey($key, 'int')),
            $value === null ? 'null' : (string)(int)$value
        );

        $replacementPairs[] = new ReplacementPair(
            sprintf('"%s"', self::decorateReplacementKey($key, 'float')),
            $value === null ? 'null' : (string)(float)$value
        );

        $replacementPairs[] = new ReplacementPair(
            sprintf('"%s"', self::decorateReplacementKey($key, 'bool')),
            (bool)$value ? 'true' : 'false'
        );

        if ($value === null) {
            $replacementPairs[] = new ReplacementPair(
                sprintf('"%s"', self::decorateReplacementKey($key, 'string')),
                'null'
            );

            $replacementPairs[] = new ReplacementPair(
                sprintf('"%s"', self::decorateReplacementKey($key)),
                'null'
            );

            return $replacementPairs;
        }

        $replacementPairs[] = new ReplacementPair(
            self::decorateReplacementKey($key),
            (string)$value
        );

        return $replacementPairs;
    }


    private static function decorateReplacementKey(string $key, ?string $datatype = null): string
    {
        if ($datatype !== null) {
            $key .= '|' . $datatype;
        }

        return '%' . $key . '%';
    }
}
