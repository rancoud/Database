<?php
$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
;
return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        'binary_operator_spaces' => array(
            'align_equals' => false,
            'align_double_arrow' => true,
        ),
        '@Symfony:risky' => true,
        'array_syntax' => ['syntax' => 'short'],
        'declare_strict_types' => true,
        'linebreak_after_opening_tag' => true,
        'mb_str_functions' => true,
        'native_function_invocation' => true,
        'no_php4_constructor' => true,
        'no_unreachable_default_argument_value' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'ordered_imports' => true,
        'php_unit_strict' => true,
        'phpdoc_order' => true,
        'semicolon_after_instruction' => true,
        'single_import_per_statement' => false,
        'strict_comparison' => true,
        'strict_param' => true,
        'concat_space' => ['spacing' => 'one'],
        'trailing_comma_in_multiline_array' => false,
        'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => false],
        'single_line_throw' => false,
        'no_superfluous_phpdoc_tags' => false
    ])
    ->setFinder($finder)
    ->setCacheFile(__DIR__.'/.php_cs.cache');
