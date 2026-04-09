<?php

declare(strict_types=1);

namespace BrandEmbassyTest\Slim\Tools;


class ReplacementPair
{
    public function __construct(
        public readonly string $key,
        public readonly mixed $value,
    ) {
    }
}
