<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-11-03 15:34:28 +0800
 */

namespace Teddy\Config\Tags;

use Closure;
use Teddy\Abstracts\AbstractConfigTag;
use Throwable;

class EvalTag extends AbstractConfigTag
{
    protected function parseValue($value)
    {
        if (is_string($value)) {
            try {
                $value = Closure::bind(function (string $value) {
                    $value = ' '.$value.';';
                    if (!preg_match('#[;\s]return\s+#', $value)) {
                        $value = 'return '.$value;
                    }

                    return eval($value);
                }, null)($value);
            } catch (Throwable $e) {
                $value = null;
            }
        }

        return null;
    }
}
