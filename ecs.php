<?php declare(strict_types = 1);

use PHP_CodeSniffer\Standards\Generic\Sniffs\CodeAnalysis\UnusedFunctionParameterSniff;
use PHP_CodeSniffer\Standards\PEAR\Sniffs\Commenting\InlineCommentSniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\Arrays\ArrayDeclarationSniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\PHP\CommentedOutCodeSniff;
use Symplify\EasyCodingStandard\Config\ECSConfig;

$defaultEcsConfigurationSetup = require 'vendor/brandembassy/coding-standard/default-ecs.php';

return static function (ECSConfig $ecsConfig) use ($defaultEcsConfigurationSetup): void {
    $defaultSkipList = $defaultEcsConfigurationSetup($ecsConfig, __DIR__);

    $ecsConfig->paths([
        __DIR__ . '/src',
    ]);

    $skipList = [
        InlineCommentSniff::class => [__DIR__ . '/default-ecs.php'],
        CommentedOutCodeSniff::class => [__DIR__ . '/ecs.php', __DIR__ . '/default-ecs.php'],
        ArrayDeclarationSniff::class => [__DIR__ . '/ecs.php', __DIR__ . '/default-ecs.php'],
        UnusedFunctionParameterSniff::class . '.FoundInImplementedInterface' => [
            __DIR__ . '/src/BrandEmbassyCodingStandard/PhpStan/Rules/Method/ImmutableWitherMethodRule.php',
        ],
        UnusedFunctionParameterSniff::class . '.FoundInImplementedInterfaceAfterLastUsed' => [
            __DIR__ . '/src/BrandEmbassyCodingStandard/PhpStan/Rules/Method/ImmutableWitherMethodRule.php',
        ],
        'SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint' => [
            __DIR__ . '/src/BrandEmbassyCodingStandard/Sniffs/Classes/ClassesWithoutSelfReferencingSniff.php',
            __DIR__ . '/src/BrandEmbassyCodingStandard/Sniffs/Classes/FinalClassByAnnotationSniff.php',
            __DIR__ . '/src/BrandEmbassyCodingStandard/Sniffs/Classes/TraitUsePositionSniff.php',
            __DIR__ . '/src/BrandEmbassyCodingStandard/Sniffs/Commenting/CreateMockFunctionReturnTypeOrderSniff.php',
            __DIR__ . '/src/BrandEmbassyCodingStandard/Sniffs/Commenting/FunctionCommentSniff.php',
            __DIR__ . '/src/BrandEmbassyCodingStandard/Sniffs/NamingConvention/CamelCapsFunctionNameSniff.php',
            __DIR__ . '/src/BrandEmbassyCodingStandard/Sniffs/WhiteSpace/BlankLineBeforeReturnSniff.php',
            __DIR__ . '/src/BrandEmbassyCodingStandard/Sniffs/WhiteSpace/BlankLineBeforeThrowSniff.php',
        ],
        __DIR__ . '/*/__fixtures__/*',
        __DIR__ . '/*/__fixtures/*',
    ];

    $ecsConfig->skip(array_merge($defaultSkipList, $skipList));
};
