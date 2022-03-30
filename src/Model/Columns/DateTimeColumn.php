<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-03-29 17:06:39 +0800
 */

namespace Teddy\Model\Columns;

use Attribute;
use DateTime;
use DateTimeZone;
use Exception;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class DateTimeColumn extends Column
{
    protected $format = 'Y-m-d H:i:s';

    protected $update = false;

    protected $timezone;

    public function __construct(...$values)
    {
        parent::__construct(...$values);

        $defaultTimezone = date_default_timezone_get();
        $this->timezone = new DateTimeZone($defaultTimezone ?: 'UTC');
    }

    public function convertToDbValue($value)
    {
        if ($this->update) {
            $value = new DateTime();
        }

        if (empty($value)) {
            return null;
        }

        try {
            $t = $this->asDateTime($value);
        } catch (Exception $e) {
            return null;
        }

        return $t->setTimezone($this->timezone)->format($this->format);
    }

    public function convertToPhpValue($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            $value = $this->asDateTime($value);
        } catch (Exception $e) {
            return null;
        }

        return $value->setTimezone($this->timezone);
    }

    public function defaultValue()
    {
        if ($this->default) {
            return new DateTime($this->default);
        }

        return null;
    }

    /**
     * @param DateTime|int|string $value
     */
    protected function asDateTime($value): DateTime
    {
        if ($value instanceof DateTime) {
            return $value;
        }

        if (is_int($value)) {
            return new DateTime('@'.$value);
        }

        if ($this->isStandardDateFormat($value)) {
            return new DateTime($value);
        }

        return DateTime::createFromFormat($this->format, $value);
    }

    protected function isStandardDateFormat($value): int|false
    {
        return is_string($value) ? preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $value) : false;
    }
}
