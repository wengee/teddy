<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Validation\Validators;

use InvalidArgumentException;
use Teddy\Validation\Field;
use Teddy\Validation\Validation;

class ArrayValidator extends Validator
{
    protected $validation;

    /**
     * @param Field[]|Validation $validation
     */
    public function __construct(Field $field, $validation)
    {
        if (is_array($validation)) {
            $validation = new Validation($validation);
        }

        if (!($validation instanceof Validation)) {
            throw new InvalidArgumentException('validation is invalid.');
        }

        $this->validation = $validation;
        parent::__construct($field);
    }

    protected function validate($value, array $data, callable $next)
    {
        $value = $this->validation->validate((array) $value);

        return $next($value, $data);
    }
}
