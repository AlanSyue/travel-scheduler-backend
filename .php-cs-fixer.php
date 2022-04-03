<?php

declare(strict_types=1);

use PhpCsFixer\Config;

$config = new Config();

return $config->setRules(
        [
            '@PSR1'                  => true,
            '@PSR2'                  => true,
            '@PhpCsFixer'            => true,
            'binary_operator_spaces' => [
                'default' => 'single_space',
                'operators' => [
                    '=>' => null,
                ],
            ],
            'cast_spaces' => [
                'space' => 'single',
            ],
            'concat_space' => [
                'spacing' => 'one',
            ],
            'explicit_string_variable'               => true,
            'multiline_whitespace_before_semicolons' => [
                'strategy' => 'no_multi_line',
            ],
            'no_superfluous_phpdoc_tags'        => false,
            'not_operator_with_successor_space' => true,
            'ordered_class_elements'            => [],
            'phpdoc_summary'                    => false,
            'phpdoc_no_empty_return'            => false,
            'php_unit_internal_class'           => false,
            'php_unit_test_class_requires_covers'=> false,
            'trailing_comma_in_multiline'       => [
                'elements' => ['arrays'],
            ],
            'whitespace_after_comma_in_array'   => true,
            'return_assignment'                 => false,
            'yoda_style' => false,
        ]
    )
    ->setCacheFile(__DIR__ . '/.php_cs.cache')
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__)
    );
