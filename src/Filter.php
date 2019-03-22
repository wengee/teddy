<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-22 10:26:26 +0800
 */
namespace Teddy;

use DateTime;

class Filter
{
    const FILTER_BOOL          = 'bool';

    const FILTER_BOOLEAN       = 'boolean';

    const FILTER_EMAIL         = 'email';

    const FILTER_ABSINT        = 'absint';

    const FILTER_INT           = 'int';

    const FILTER_INT_CAST      = 'int!';

    const FILTER_STRING        = 'string';

    const FILTER_FLOAT         = 'float';

    const FILTER_FLOAT_CAST    = 'float!';

    const FILTER_DOUBLE        = 'double';

    const FILTER_DOUBLE_CAST   = 'double!';

    const FILTER_ALPHANUM      = 'alphanum';

    const FILTER_TRIM          = 'trim';

    const FILTER_STRIPTAGS     = 'striptags';

    const FILTER_LOWER         = 'lower';

    const FILTER_UPPER         = 'upper';

    const FILTER_URL           = 'url';

    const FILTER_SPECIAL_CHARS = 'special_chars';

    protected $_filters;

    /**
     * Adds a user-defined filter
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
     * Sanitizes a value with a specified single or set of filters
     */
    public function sanitize($value, $filters, bool $noRecursive = false)
    {
        $filters = (is_string($filters) && strpos($filters, ',') !== false) ? explode(',', $filters) : $filters;
        if (is_array($filters)) {
            if ($value !== null) {
                foreach ($filters as $filter) {
                    if (is_array($value) && !$noRecursive) {
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

        if (is_array($value) && !$noRecursive) {
            $arrayValue = [];
            foreach ($value as $itemKey => $itemValue) {
                $arrayValue[$itemKey] = $this->_sanitize($itemValue, $filters);
            }
            return $arrayValue;
        }

        return $this->_sanitize($value, $filters);
    }

    /**
     * Internal sanitize wrapper to filter_var
     */
    protected function _sanitize($value, string $filter)
    {
        if (isset($this->_filters[$filter])) {
            $filterObject = $this->_filters[$filter];
            if (is_callable($filterObject)) {
                return $filterObject($value);
            }

            return $filterObject->filter($value);
        } elseif (preg_match('/^date\\[(.+)\\]$/', $filter, $m)) {
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
                return trim($value);

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

            default:
                throw new Exception('Sanitize filter "' . $filter . '" is not supported');
        }
    }

    /**
     * Return the user-defined filters in the instance
     */
    public function getFilters(): array
    {
        return $this->_filters;
    }
}
