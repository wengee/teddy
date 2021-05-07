<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-05-07 23:33:53 +0800
 */

namespace Teddy\Validation\Validators;

use InvalidArgumentException;
use Teddy\Validation\Fields\Field;
use Teddy\Validation\Validation;

class ListValidator extends Validator
{
    protected $validateType;

    protected $validation;

    /**
     * @param callable|Field|Field[]|Validation $validation
     */
    public function __construct(Field $field, $validation)
    {
        if ($validation instanceof Field) {
            $this->validateType = 2;
        } else {
            if (is_array($validation) && !is_callable($validation)) {
                $validation = new Validation($validation);
            }

            if ($validation instanceof Validation) {
                $this->validateType = 3;
            } else {
                if (!is_callable($validation)) {
                    throw new InvalidArgumentException('validation is invalid.');
                }

                $this->validateType = 1;
            }
        }

        $this->validation = $validation;
        parent::__construct($field);
    }

    protected function validate($value, array $data, callable $next)
    {
        $ret   = [];
        $value = (array) $value;
        foreach ($value as $val) {
            $val = $this->validateItem($val, $data);
            if (null !== $val) {
                $ret[] = $val;
            }
        }

        return $next($ret, $data);
    }

    protected function validateItem($value, array $data)
    {
        $ret = null;

        switch ($this->validateType) {
            case 1:
                $ret = call_user_func($this->validation, $value);

                break;

            case 2:
                /** @var Field $field */
                $field = $this->validation;
                $ret   = $field->filter($value);
                $ret   = $field->validate($ret, $data);

                break;

            case 3:
                /** @var Validation $validation */
                $validation = $this->validation;
                $ret        = $validation->validate((array) $value);

                break;
        }

        return $ret;
    }
}
