<?php

namespace BrandEmbassyTest\Slim\Tools;


class ReplacementPair
{
    public function __construct(
        public readonly string $key,
        public readonly mixed $value,
    ) {
    }
}
