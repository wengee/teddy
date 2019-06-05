<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-05 10:26:37 +0800
 */
namespace Teddy\Validation\Validators;

use Teddy\Traits\HasOptions;
use Teddy\Validation\Exception;
use Teddy\Validation\ValidatorInterface;

abstract class ValidatorBase implements ValidatorInterface
{
    use HasOptions;

    protected $field;

    protected $label;

    protected $message = [];

    protected $customMessage = [];

    protected $formatedMessage = [];

    public static function make(...$args)
    {
        return new static(...$args);
    }

    public function setName(string $name)
    {
        $this->field = $name;
        $this->label = $this->label ?: ucfirst($name);
        return $this;
    }

    public function setLabel(string $label)
    {
        $this->label = $label;
        return $this;
    }

    public function setMessage($message, ?string $key = null)
    {
        if (is_array($message)) {
            $this->customMessage = array_merge($this->customMessage, $message);
        } else {
            $key = $key ?: 'default';
            $this->customMessage[$key] = strval($message);
        }

        return $this;
    }

    protected function throwMessage(?string $key = null)
    {
        $key = $key ?: 'default';
        if (isset($this->customMessage[$key])) {
            $message = $this->customMessage[$key];
        } elseif (isset($this->formatedMessage[$key])) {
            $message = $this->formatedMessage[$key];
        } else {
            $message = isset($this->message[$key]) ?
                $this->message[$key] :
                (isset($this->message['default']) ?
                    $this->message['default'] :
                    ':label 不合法');

            $properties = get_object_vars($this);
            $data = [];
            foreach ($properties as $k => $v) {
                $data[':' . $k] = $v;
            }

            $message = strtr($message ?: ':label 不合法', $data);
            $this->formatedMessage[$key] = $message;
        }

        throw new Exception($message);
    }

    abstract public function validate($value, array $data);
}
