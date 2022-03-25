<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-03-14 15:23:05 +0800
 */

namespace Teddy\Crontab;

class Parser
{
    protected $formatedDates = [];

    public static function check(string $crontabStr, int $minutes, int $hours, int $day, int $month, int $week)
    {
        $date = self::parse($crontabStr);
        if (
            null !== $date
            && isset($date['minutes'][$minutes], $date['hours'][$hours], $date['day'][$day], $date['month'][$month], $date['week'][$week])
        ) {
            return $date['second'];
        }

        return false;
    }

    public static function checkTimestamp(string $crontabStr, int $timestamp = 0)
    {
        $timestamp = $timestamp > 0 ? $timestamp : time();
        $minutes   = (int) date('i', $timestamp);
        $hours     = (int) date('G', $timestamp);
        $day       = (int) date('j', $timestamp);
        $month     = (int) date('n', $timestamp);
        $week      = (int) date('w', $timestamp);

        return self::check($crontabStr, $minutes, $hours, $day, $month, $week);
    }

    protected static function parse(string $crontabStr): ?array
    {
        $crontabStr = trim($crontabStr);
        $date       = self::$formatedDates[$crontabStr] ?? null;

        if (preg_match('/^(((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+)?((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)$/i', trim($crontabStr), $m)) {
            $date = [
                'second'    => (empty($m[2])) ? [0 => 0] : self::parseCronNum($m[2], 0, 59),
                'minutes'   => self::parseCronNum($m[5], 0, 59),
                'hours'     => self::parseCronNum($m[8], 0, 23),
                'day'       => self::parseCronNum($m[11], 1, 31),
                'month'     => self::parseCronNum($m[14], 1, 12),
                'week'      => self::parseCronNum($m[17], 0, 6),
            ];
        }

        return $date;
    }

    protected static function parseCronNum($s, $min, $max): array
    {
        $result = [];
        $v1     = explode(',', $s);
        foreach ($v1 as $v2) {
            $v3   = explode('/', $v2);
            $step = empty($v3[1]) ? 1 : $v3[1];
            $v4   = explode('-', $v3[0]);
            $_min = 2 == count($v4) ? $v4[0] : ('*' == $v3[0] ? $min : $v3[0]);
            $_max = 2 == count($v4) ? $v4[1] : ('*' == $v3[0] ? $max : $v3[0]);
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
