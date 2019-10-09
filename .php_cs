<?php

date_default_timezone_set('Asia/Shanghai');
$timestamp = date('Y-m-d H:i:s O');

$header = <<<EOF
This file is part of Teddy Framework.

@author   Fung Wing Kit <wengee@gmail.com>
@version  $timestamp
EOF;

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR2' => true,
        '@PHP71Migration:risky' => true,
        '@PHP73Migration' => true,

        'header_comment' => [
            'commentType' => 'PHPDoc',
            'header' => $header,
            'separate' => 'none'
        ],
        'array_syntax' => [
            'syntax' => 'short'
        ],
        'single_quote' => true,
        'class_attributes_separation' => true,
        'no_unused_imports' => true,
        'standardize_not_equals' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude('/vendor/*')
            ->exclude('/src/phar-cli-stub.php')
            ->in(__DIR__)
    )
    ->setUsingCache(false);
