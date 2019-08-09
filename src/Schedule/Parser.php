<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-08 10:16:48 +0800
 */

namespace Teddy\Schedule;

class Parser
{
    protected $formatedDates = [];

    public static function parse(string $crontabStr, int $time = 0)
    {
        $crontabStr = trim($crontabStr);
        $date = isset(self::$formatedDates[$crontabStr]) ? self::$formatedDates[$crontabStr] : null;

        if (preg_match('/^(((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+)?((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)$/i', trim($crontabStr), $m)) {
            $date = [
                'second' => (empty($m[2])) ? [0 => 0] : self::parseCronNum($m[2], 0, 59),
                'minutes' => self::parseCronNum($m[5], 0, 59),
                'hours' => self::parseCronNum($m[8], 0, 23),
                'day' => self::parseCronNum($m[11], 1, 31),
                'month' => self::parseCronNum($m[14], 1, 12),
                'week' => self::parseCronNum($m[17], 0, 6),
            ];
        }

        if ($date !== null) {
            $time = $time > 0 ? $time : time();
            if (
                isset($date['minutes'][intval(date('i', $time))]) &&
                isset($date['hours'][intval(date('G', $time))]) &&
                isset($date['day'][intval(date('j', $time))]) &&
                isset($date['week'][intval(date('w', $time))]) &&
                isset($date['month'][intval(date('n', $time))])
            ) {
                return $date['second'];
            }
        }

        return false;
    }

    protected static function parseCronNum($s, $min, $max): array
    {
        $result = [];
        $v1 = explode(',', $s);
        foreach ($v1 as $v2) {
            $v3 = explode('/', $v2);
            $step = empty($v3[1]) ? 1 : $v3[1];
            $v4 = explode('-', $v3[0]);
            $_min = count($v4) == 2 ? $v4[0] : ($v3[0] == '*' ? $min : $v3[0]);
            $_max = count($v4) == 2 ? $v4[1] : ($v3[0] == '*' ? $max : $v3[0]);
            for ($i = $_min; $i <= $_max; $i += $step) {
                if (intval($i) < $min) {
                    $result[$min] = $min;
                } elseif (intval($i) > $max) {
                    $result[$max] = $max;
                } else {
                    $result[$i] = intval($i);
                }
            }
        }
        ksort($result);
        return $result;
    }
}
