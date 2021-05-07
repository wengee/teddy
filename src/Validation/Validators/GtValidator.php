<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-05-06 16:42:41 +0800
 */

namespace Teddy\Validation\Validators;

use Teddy\Validation\Fields\Field;

class GtValidator extends Validator
{
    protected $value;

    protected $message = ':label必须大于:value';

    public function __construct(Field $field, $value, ?string $message = null)
    {
        $this->value = $value;
        parent::__construct($field, $message);
    }

    protected function validate($value, array $data, callable $next)
    {
        if (!$this->checkCondition($value)) {
            $this->throwError([
                ':value' => $this->value,
            ]);
        }

        return $next($value, $data);
    }

    protected function checkCondition($value)
    {
        return $value > $this->value;
    }
}
