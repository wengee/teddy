<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-07-21 17:46:00 +0800
 */

namespace Teddy\Config\Tags;

use Closure;
use Teddy\Interfaces\ConfigTagInterface;
use Throwable;

class EvalTag implements ConfigTagInterface
{
    public function parseValue($value)
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

            return $value;
        }

        return null;
    }
}
