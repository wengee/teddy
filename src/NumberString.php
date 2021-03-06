<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-08-12 16:37:01 +0800
 */

namespace Teddy;

class NumberString
{
    protected $strIndex = 'l2Vj5aUOBCLpdFRsK6ytAXzGbY1QWewvHhxE4gT38SPqmfioc7Ju9NDr0IZMkn';

    protected $maxBase = 62;

    public function __construct(string $strIndex = null)
    {
        if ($strIndex === null) {
            $strIndex = (string) config('app.strIndex');
        }

        if ($strIndex) {
            $this->strIndex = $strIndex;
            $this->maxBase = strlen($strIndex);
        }
    }

    /**
     * @param int $num
     * @param string|int|null $base
     *
     * @return string
     */
    public function encode(int $num, $base = null): string
    {
        if ($base === null) {
            $index = $this->strIndex;
            $base = $this->maxBase;
        } elseif (is_string($base)) {
            $index = $base;
            $base = strlen($index);
        } else {
            $base = (int) $base;
            $index = substr($this->strIndex, 0, $base);
        }

        $out = '';
        for ($t = floor(log10($num) / log10($base)); $t >= 0; $t--) {
            $a = intval(floor($num /  $base ** $t));
            $out = $out . substr($index, $a, 1);
            $num = $num - ($a *  $base** $t);
        }

        return $out;
    }

    /**
     * @param string $num
     * @param string|int|null $base
     *
     * @return int
     */
    public function decode(string $num, $base = null): int
    {
        if ($base === null) {
            $index = $this->strIndex;
            $base = $this->maxBase;
        } elseif (is_string($base)) {
            $index = $base;
            $base = strlen($index);
        } else {
            $base = (int) $base;
            $index = substr($this->strIndex, 0, $base);
        }

        $out = 0;
        $len = strlen($num) - 1;
        for ($t = 0; $t <= $len; $t++) {
            $out = $out + strpos($index, substr($num, $t, 1)) *  $base ** ($len - $t);
        }

        return (int) $out;
    }
}
