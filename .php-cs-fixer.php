<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-07-26 10:51:38 +0800
 */

date_default_timezone_set('Asia/Shanghai');
$timestamp = date('Y-m-d H:i:s O');

$header = <<<EOF
    This file is part of Teddy Framework.

    @author   Fung Wing Kit <wengee@gmail.com>
    @version  {$timestamp}
    EOF;

$finder = PhpCsFixer\Finder::create()
    ->exclude('/vendor/*')
    ->in(__DIR__)
;

$config = new PhpCsFixer\Config();

return $config->setRules([
    '@PSR2'                 => true,
    '@PhpCsFixer'           => true,
    '@PHP71Migration:risky' => true,
    '@PHP73Migration'       => true,

    'header_comment' => [
        'comment_type' => 'PHPDoc',
        'header'       => $header,
        'separate'     => 'bottom',
    ],
    'blank_line_after_opening_tag' => false,
    'linebreak_after_opening_tag'  => false,
    'binary_operator_spaces'       => [
        'operators' => [
            '='  => 'align_single_space_minimal',
            '=>' => 'align_single_space_minimal',
        ],
    ],
    'phpdoc_to_comment' => [
        'ignored_tags' => ['var', 'param'],
    ],
])
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setUsingCache(false)
;
