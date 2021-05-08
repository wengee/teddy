<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-05-08 16:11:28 +0800
 */

namespace Teddy\Validation;

use Illuminate\Support\Arr;

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
            $name = $field->getName() ?: strval($name);
            $this->addField($name, $field);
        }

        $this->initialize();
    }

    public function addField(string $name, Field $field): Field
    {
        $field->setName($name);
        $this->fields[$name] = $field;

        return $field;
    }

    public function add(string $name, ?string $label = null): Field
    {
        $label = $label ?: ucfirst($name);
        $field = Field::make($label, $name);

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

        $validated = [];
        foreach ($fields as $field) {
            $validated = $field->validate($data, $validated, $safe);
        }

        return $this->afterValidate($validated);
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
        foreach ($this->fields as $name => $field) {
            if (Arr::has($data, $name)) {
                $value = Arr::get($data, $name);
                $value = $field->filterValue($value);

                Arr::set($data, $name, $value);
            }
        }

        return $data;
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
