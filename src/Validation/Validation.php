<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-05-07 16:47:08 +0800
 */

namespace Teddy\Validation;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Teddy\Validation\Fields\AnyField;
use Teddy\Validation\Fields\ArrayField;
use Teddy\Validation\Fields\BooleanField;
use Teddy\Validation\Fields\Field;
use Teddy\Validation\Fields\FloatField;
use Teddy\Validation\Fields\IntegerField;
use Teddy\Validation\Fields\ListField;
use Teddy\Validation\Fields\StringField;
use Teddy\Validation\Fields\TrimField;

/**
 * @method AnyField     any(string $name, ?string $label = null)
 * @method ArrayField   array(string $name, ?string $label = null)
 * @method BooleanField bool(string $name, ?string $label = null)
 * @method BooleanField boolean(string $name, ?string $label = null)
 * @method FloatField   float(string $name, ?string $label = null)
 * @method FloatField   double(string $name, ?string $label = null)
 * @method IntegerField int(string $name, ?string $label = null)
 * @method IntegerField integer(string $name, ?string $label = null)
 * @method ListField    list(string $name, ?string $label = null)
 * @method StringField  string(string $name, ?string $label = null)
 * @method StringField  str(string $name, ?string $label = null)
 * @method TrimField    trim(string $name, ?string $label = null)
 */
class Validation
{
    /**
     * @var Field[]
     */
    protected $fields = [];

    /**
     * @param Field[] $fields
     */
    public function __construct(array $fields = [])
    {
        foreach ($fields as $name => $field) {
            $this->add($name, $field);
        }

        $this->initialize();
    }

    public function __call(string $method, array $arguments)
    {
        $name = array_shift($arguments);
        if (!$name) {
            throw new InvalidArgumentException('name is required.');
        }

        return $this->add($name, Field::factory($method, ...$arguments));
    }

    public function add(string $name, Field $field): Field
    {
        $this->fields[$name] = $field;

        return $field;
    }

    public function merge(Validation $validation): self
    {
        $this->fields = array_merge($this->fields, $validation->getFields());

        return $this;
    }

    /**
     * @return Field[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param Field[] $fields
     */
    public function validate(?array $data, ?array $fields = null, bool $safe = false): array
    {
        if (!$data) {
            return [];
        }

        $data = $this->beforeValidate($data);
        $data = $this->filterData($data);

        if ($fields) {
            $fields = array_merge($this->fields, $fields);
        } else {
            $fields = $this->fields;
        }

        foreach ($fields as $name => $field) {
            $value = Arr::get($data, $name);
            $value = $field->validate($value, $data, $safe);

            if (null === $value) {
                Arr::forget($data, $name);
            } else {
                Arr::set($data, $name, $value);
            }
        }

        return $this->afterValidate($data);
    }

    /**
     * @param Field[] $fields
     */
    public function check(array $data, ?array $fields = null)
    {
        return $this->validate($data, $fields, true);
    }

    protected function filterData(array $data): array
    {
        $ret = [];
        foreach ($this->fields as $name => $field) {
            $value = Arr::get($data, $name);
            $value = $field->filter($value);

            Arr::set($ret, $name, $value);
        }

        return $ret;
    }

    protected function initialize(): void
    {
    }

    protected function beforeValidate(array $data): array
    {
        return $data;
    }

    protected function afterValidate(array $data): array
    {
        return $data;
    }
}
