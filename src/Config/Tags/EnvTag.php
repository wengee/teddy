<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-07-21 16:06:40 +0800
 */

namespace Teddy\Config\Tags;

use Teddy\Abstracts\AbstractConfigTag;

class EnvTag extends AbstractConfigTag
{
    protected function parseValue($value)
    {
        if (is_string($value)) {
            return env($value);
        }

        if (is_array($value) && isset($value['key'])) {
            $envVal = env($value['key'], $value['default'] ?? null);

            if (isset($value['filter'])) {
                $envVal = $this->filterValue($value['filter'], $envVal);
            }

            if (isset($value['if'])) {
                $ifValue = $value['if'] ?? null;
                if ($envVal === $ifValue) {
                    $envVal = $value['then'] ?? $envVal;
                } else {
                    $envVal = $value['else'] ?? $envVal;
                }
            }

            return $envVal;
        }

        return null;
    }

    protected function filterValue(string $type, $value)
    {
        if (null === $value) {
            return null;
        }

        switch ($type) {
            case 'str':
            case 'string':
                return (string) $value;

            case 'bool':
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);

            case 'int':
            case 'integer':
                return intval($value);

            case 'float':
            case 'double':
                return floatval($value);
        }

        return $value;
    }
}
