<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-05-10 14:53:50 +0800
 */

namespace Teddy;

use DateTime;
use Teddy\Traits\Singleton;

class Filter
{
    use Singleton;

    public const FILTER_BOOL          = 'bool';
    public const FILTER_BOOLEAN       = 'boolean';
    public const FILTER_EMAIL         = 'email';
    public const FILTER_ABSINT        = 'absint';
    public const FILTER_INT           = 'int';
    public const FILTER_INT_CAST      = 'int!';
    public const FILTER_STRING        = 'string';
    public const FILTER_FLOAT         = 'float';
    public const FILTER_FLOAT_CAST    = 'float!';
    public const FILTER_DOUBLE        = 'double';
    public const FILTER_DOUBLE_CAST   = 'double!';
    public const FILTER_ALPHANUM      = 'alphanum';
    public const FILTER_TRIM          = 'trim';
    public const FILTER_STRIPTAGS     = 'striptags';
    public const FILTER_LOWER         = 'lower';
    public const FILTER_UPPER         = 'upper';
    public const FILTER_URL           = 'url';
    public const FILTER_SPECIAL_CHARS = 'special_chars';
    public const FILTER_LIST          = 'list';
    public const FILTER_ARRAY         = 'array';
    public const FILTER_TIMESTAMP     = 'timestamp';
    public const FILTER_JSON_DECODE   = 'json_decode';
    public const FILTER_JSON_ENCODE   = 'json_encode';
    public const FILTER_UUID          = 'uuid';

    protected $_filters;

    /**
     * Adds a user-defined filter.
     *
     * @param mixed $handler
     */
    public function add(string $name, $handler): Filter
    {
        if (!is_object($handler) && !is_callable($handler)) {
            throw new Exception('Filter must be an object or callable');
        }

        $this->_filters[$name] = $handler;

        return $this;
    }

    /**
     * Sanitizes a value with a specified single or set of filters.
     *
     * @param mixed $value
     * @param mixed $filters
     */
    public function sanitize($value, $filters, bool $noRecursive = true)
    {
        $filters = (is_string($filters) && false !== strpos($filters, ',')) ? explode(',', $filters) : $filters;
        if (is_array($filters)) {
            if (null !== $value) {
                foreach ($filters as $filter) {
                    if (self::FILTER_LIST !== $filter && is_array($value) && !$noRecursive) {
                        $arrayValue = [];
                        foreach ($value as $itemKey => $itemValue) {
                            $arrayValue[$itemKey] = $this->_sanitize($itemValue, $filter);
                        }
                        $value = $arrayValue;
                    } else {
                        $value = $this->_sanitize($value, $filter);
                    }
                }
            }

            return $value;
        }

        if (self::FILTER_LIST !== $filters && is_array($value) && !$noRecursive) {
            $arrayValue = [];
            foreach ($value as $itemKey => $itemValue) {
                $arrayValue[$itemKey] = $this->_sanitize($itemValue, $filters);
            }

            return $arrayValue;
        }

        return $this->_sanitize($value, $filters);
    }

    /**
     * Return the user-defined filters in the instance.
     */
    public function getFilters(): array
    {
        return $this->_filters;
    }

    /**
     * Internal sanitize wrapper to filter_var.
     *
     * @param mixed $value
     */
    protected function _sanitize($value, string $filter)
    {
        if (isset($this->_filters[$filter])) {
            $filterObject = $this->_filters[$filter];
            if (is_callable($filterObject)) {
                return $filterObject($value);
            }

            return $filterObject->filter($value);
        }
        if (preg_match('/^date\\[(.+)\\]$/', $filter, $m)) {
            return DateTime::createFromFormat($m[1], $value);
        }

        switch ($filter) {
            case self::FILTER_BOOL:
            case self::FILTER_BOOLEAN:
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);

            case self::FILTER_EMAIL:
                return filter_var($value, FILTER_SANITIZE_EMAIL);

            case self::FILTER_INT:
            case self::FILTER_INT_CAST:
                return intval($value);

            case self::FILTER_ABSINT:
                return abs(intval($value));

            case self::FILTER_STRING:
                return filter_var($value, FILTER_SANITIZE_STRING);

            case self::FILTER_FLOAT:
            case self::FILTER_FLOAT_CAST:
            case self::FILTER_DOUBLE:
            case self::FILTER_DOUBLE_CAST:
                return doubleval($value);

            case self::FILTER_ALPHANUM:
                return preg_replace('/[^A-Za-z0-9]/', '', $value);

            case self::FILTER_TRIM:
                return trim(strval($value));

            case self::FILTER_STRIPTAGS:
                return strip_tags($value);

            case self::FILTER_LOWER:
                if (function_exists('mb_strtolower')) {
                    return mb_strtolower($value);
                }

                return strtolower($value);

            case self::FILTER_UPPER:
                if (function_exists('mb_strtoupper')) {
                    return mb_strtoupper($value);
                }

                return strtoupper($value);

            case self::FILTER_URL:
                return filter_var($value, FILTER_SANITIZE_URL);

            case self::FILTER_SPECIAL_CHARS:
                return filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);

            case self::FILTER_LIST:
                return array_values((array) $value);

            case self::FILTER_ARRAY:
                if (null === $value || '' === $value) {
                    return [];
                }

                return is_array($value) ? $value : [$value];

            case self::FILTER_TIMESTAMP:
                if (is_numeric($value)) {
                    return intval($value);
                }

                if (empty($value)) {
                    return null;
                }

                return strtotime((string) $value);

            case self::FILTER_JSON_DECODE:
                return json_decode($value, true);

            case self::FILTER_JSON_ENCODE:
                return json_encode($value);

            case self::FILTER_UUID:
                $value = trim((string) $value);
                if (preg_match('#^\w{8}(\-\w{4}){3}\-\w{12}$#i', $value)) {
                    return $value;
                }

                return null;

            default:
                throw new Exception('Sanitize filter "'.$filter.'" is not supported');
        }
    }
}
