<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-07 18:01:00 +0800
 */

namespace Teddy\Validation\Validators;

class IdCard extends ValidatorRuleBase
{
    protected $message = ':label不是正确的身份证号码';

    protected function validate($value, array $data, callable $next)
    {
        $value = strtoupper(trim($value));
        if (!$this->checkCitizenId($value)) {
            $this->throwMessage();
        }

        return $next($value, $data);
    }

    protected function checkCitizenId(string $idcard): bool
    {
        if (! preg_match('/^\\d{17}[\\dxX]$/', $idcard)) {
            return false;
        }

        $weights = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
        $results = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];
        $arr1 = str_split($idcard);
        $arr2 = [];
        for ($i = 0; $i <= 16; $i ++) {
            $arr2[] = intval($arr1[$i]) * $weights[$i];
        }
        $x = array_sum($arr2) % 11;
        $x = $results[$x];
        return $x == $arr1[17];
    }
}