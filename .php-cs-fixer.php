<?php

date_default_timezone_set('Asia/Shanghai');
$timestamp = date('Y-m-d H:i:s O');

$header = <<<EOF
This file is part of Teddy Framework.

@author   Fung Wing Kit <wengee@gmail.com>
@version  $timestamp
EOF;

$finder = PhpCsFixer\Finder::create()
    ->exclude('/vendor/*')
    ->in(__DIR__);

$config = new PhpCsFixer\Config();
return $config->setRules([
        '@PSR2' => true,
        '@PhpCsFixer' => true,
        '@PHP71Migration:risky' => true,
        '@PHP73Migration' => true,

        'header_comment' => [
            'comment_type' => 'PHPDoc',
            'header' => $header,
            'separate' => 'bottom'
        ],
        'blank_line_after_opening_tag' => false,
        'linebreak_after_opening_tag' => false,
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setUsingCache(false);
