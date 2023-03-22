<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-03-22 16:30:13 +0800
 */

namespace Teddy\Validation\Validators;

use Teddy\Validation\Field;

class DecimalValidator extends Validator
{
    protected int $places = -1;

    protected string $message = ':label不是有效的数字格式';

    /**
     * @param int|string $places
     */
    public function __construct(Field $field, $places = -1, ?string $message = null)
    {
        if (is_string($places)) {
            $message = $places;
            $places  = -1;
        }

        $this->places = $places;
        parent::__construct($field, $message);
    }

    public function validate($value, array $data, callable $next)
    {
        $strVal   = trim(strval($value));
        $isNumber = preg_match('/^-?\\d+(\\.(\\d+))?$/', $strVal, $m);
        if (!$isNumber || ($this->places >= 0 && isset($m[2]) && strlen($m[2]) > $this->places)) {
            $this->throwError([
                ':places' => $this->places,
            ]);
        }

        $value = floatval($value);

        return $next($value, $data);
    }
}
