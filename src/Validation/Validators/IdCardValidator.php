<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Validation\Validators;

class IdCardValidator extends Validator
{
    public const REGEX = '/^[1-8][0-9]{5}((18|19|20)[0-9]{2})(0[1-9]|1[0-2])(0[1-9]|[12][0-9]|3[01])[0-9]{3}[0-9xX]$/';

    protected $message = ':label不是正确的身份证号码';

    public function validate($value, array $data, callable $next)
    {
        $value = strtoupper(trim($value));
        if (!preg_match(self::REGEX, $value, $m)) {
            $this->throwError();
        }

        if (!isset($m) || !checkdate(intval($m[3] ?? 0), intval($m[4] ?? 0), intval($m[1] ?? 0))) {
            $this->throwError();
        }

        if (!$this->checkCitizenId($value)) {
            $this->throwError();
        }

        return $next($value, $data);
    }

    protected function checkCitizenId(string $idcard): bool
    {
        if (!preg_match('/^\\d{17}[\\dxX]$/', $idcard)) {
            return false;
        }

        $weights = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
        $results = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];
        $arr1    = str_split($idcard);
        $arr2    = [];
        for ($i = 0; $i <= 16; ++$i) {
            $arr2[] = intval($arr1[$i]) * $weights[$i];
        }
        $x = array_sum($arr2) % 11;
        $x = $results[$x];

        return $x == $arr1[17];
    }
}
