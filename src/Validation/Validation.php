<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-22 18:13:12 +0800
 */
namespace Teddy\Validation;

class Validation
{
    protected $defaultValidators = [
        'alnum'         => '\Teddy\Validation\Validators\Alnum',
        'alpha'         => '\Teddy\Validation\Validators\Alpha',
        'between'       => '\Teddy\Validation\Validators\Between',
        'callback'      => '\Teddy\Validation\Validators\Callback',
        'confirmation'  => '\Teddy\Validation\Validators\Confirmation',
        'date'          => '\Teddy\Validation\Validators\Date',
        'digit'         => '\Teddy\Validation\Validators\Digit',
        'email'         => '\Teddy\Validation\Validators\Email',
        'notin'         => '\Teddy\Validation\Validators\ExclusionIn',
        'in'            => '\Teddy\Validation\Validators\InclusionIn',
        'length'        => '\Teddy\Validation\Validators\Length',
        'mobile'        => '\Teddy\Validation\Validators\Mobile',
        'number'        => '\Teddy\Validation\Validators\Numericality',
        'regex'         => '\Teddy\Validation\Validators\Regex',
        'required'      => '\Teddy\Validation\Validators\Required',
        'url'           => '\Teddy\Validation\Validators\Url',
    ];

    protected $validators = [];

    protected $rules = [];

    public function register($name, $validator = null)
    {
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                $this->register($key, $value);
            }
        } elseif (is_string($name)) {
            if (is_callable($validator)) {
                $this->validators[$name] = $validator;
            } elseif (is_subclass_of($validator, ValidatorInterface::class)) {
                $this->validators[$name] = is_object($validator) ? $validator : new $validator;
            }
        }

        return $this;
    }

    public function validate(array $input, array $rules): array
    {
        if (empty($rules)) {
            return $input;
        }

        $ret = [];
        $filter = app('filter');

        foreach ($rules as $field => $data) {
            if (is_int($field) || (is_string($field) && is_callable($data))) {
                $field = is_int($field) ? (string) $data : $field;
                if (strpos($field, ':') !== false) {
                    [$field, $filters] = explode(':', $field, 2);
                } else {
                    $filters = null;
                }

                $value = array_get($input, $field);
                if ($filter && $filters) {
                    $value = $filter->sanitize($value, $filters);
                }

                if (is_callable($data)) {
                    $this->callValidator('callback', $value, [
                        'field' => $field,
                        'label' => ucfirst($field),
                        'func' => $data,
                        'input' => $input,
                    ]);
                }

                array_set($ret, $field, $value);
                continue;
            }

            $data = (array) $data;
            $if = array_pull($data, 'if');
            if ($if !== null) {
                if (is_callable($if)) {
                    $if = (bool) $if($input);
                } else {
                    if (is_array($if)) {
                        $ifField = array_shift($if);
                        $ifValue = array_shift($if);
                    } else {
                        $ifField = (string) $if;
                        $ifValue = null;
                    }

                    $not = $ifField{0} === '!';
                    $ifField = $not ? substr($ifField, 1) : $ifField;
                    $inputValue = array_get($input, $ifField);

                    if ($ifValue === null) {
                        $if = $not ? !$inputValue : (bool) $inputValue;
                    } else {
                        $if = $not ? $inputValue != $ifValue : $inputValue == $ifValue;
                    }
                }

                if (!$if) {
                    continue;
                }
            }

            $filters = null;
            if (strpos($field, ':') !== false) {
                [$field, $filters] = explode(':', $field, 2);
            }

            $defaultValue = array_pull($data, 'default');
            $value = array_get($input, $field, $defaultValue);
            $label = array_pull($data, 'label', ucfirst($field));
            $filters = array_pull($data, 'filter', $filters);

            if ($filter && $filters) {
                $value = $filter->sanitize($value, $filters);
            }

            $this->_validateValue($value, $data, $input, $field, $label);
            array_set($ret, $field, $value);
        }

        return $ret;
    }

    protected function _validateValue($value, array $rules, array $input, string $field, string $label)
    {
        foreach ($rules as $key => $val) {
            $message = null;
            $options = ['field' => $field, 'label' => $label, 'input' => $input];

            if (is_int($key)) {
                if (is_callable($val)) {
                    $validatorName = 'callback';
                    $options['func'] = $val;
                } else {
                    $validatorName = $val;
                }
            } else {
                $validatorName = $key;
                if (is_callable($val)) {
                    $options['func'] = $val;
                } elseif (is_string($val)) {
                    $message = $val;
                } elseif (is_array($val)) {
                    if (isset($val[0])) {
                        $message = array_pull($val, 0);
                    } else {
                        $message = array_pull($val, 'message');
                    }

                    $options = array_merge($options, $val);
                }
            }

            $options['message'] = $message;
            $this->callValidator($validatorName, $value, $options);
        }
    }

    protected function callValidator(string $validator, $value, array $options = [])
    {
        $validatorObj = null;
        if (isset($this->validators[$validator])) {
            $validatorObj = $this->validators[$validator];
        } elseif (isset($this->defaultValidators[$validator])) {
            $validatorClass = $this->defaultValidators[$validator];
            $validatorObj = new $validatorClass;
        }

        if (is_callable($validatorObj)) {
            return $validatorObj($value, $options);
        } elseif ($validatorObj instanceof ValidatorInterface) {
            return $validatorObj->validate($value, $options);
        }

        throw new Exception("This validator[{$validator}] is not defined.");
    }
}
