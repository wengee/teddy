<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-11-16 14:43:48 +0800
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
            $this->add($name, $field);
        }

        $this->initialize();
    }

    public function addField(string $name, Field $field): Field
    {
        return $this->add($name, $field);
    }

    /**
     * @param null|Field|string $field
     */
    public function add(string $name, $field = null): Field
    {
        if (!($field instanceof Field)) {
            $field = Field::make((string) $field);
        }

        $field->setName($name);
        $this->fields[$name] = $field;

        return $field;
    }

    public function merge(Validation $validation): self
    {
        $this->fields = array_merge($this->fields, $validation->getFields());

        return $this;
    }

    public function getField(string $name): ?Field
    {
        return $this->fields[$name] ?? null;
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
        $data = $data ?: [];
        $data = $this->beforeValidate($data);

        if ($fields) {
            $fields = array_merge($this->fields, $fields);
        } else {
            $fields = $this->fields;
        }

        $filtered  = $this->filterData($fields, $data);
        $validated = [];
        foreach ($fields as $field) {
            $validated = $field->validate($filtered, $validated, $safe);
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

    /**
     * @param Field[] $fields
     */
    protected function filterData(array $fields, array $data): array
    {
        foreach ($fields as $name => $field) {
            if (Arr::has($data, $name)) {
                $value    = Arr::get($data, $name);
                $newValue = $field->filterValue($value);

                if ($newValue !== $value) {
                    Arr::set($data, $name, $newValue);
                }
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
