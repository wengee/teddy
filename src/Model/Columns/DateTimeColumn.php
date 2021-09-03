<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Model\Columns;

use Carbon\Carbon;
use DateTimeInterface;
use Exception;

/**
 * @Annotation
 * @Target("CLASS")
 */
class DateTimeColumn extends Column
{
    protected $format = 'Y-m-d H:i:s';

    public function convertToDbValue($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            $t = $this->asDateTime($value);
        } catch (Exception $e) {
            return null;
        }

        return $t->format($this->format);
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

        return $value;
    }

    public function defaultValue()
    {
        if ('now' === $this->default) {
            return new Carbon();
        }

        return null;
    }

    protected function asDateTime($value): Carbon
    {
        // If this value is already a Carbon instance, we shall just return it as is.
        // This prevents us having to re-instantiate a Carbon instance when we know
        // it already is one, which wouldn't be fulfilled by the DateTime check.
        if ($value instanceof Carbon) {
            return $value;
        }

        // If the value is already a DateTime instance, we will just skip the rest of
        // these checks since they will be a waste of time, and hinder performance
        // when checking the field. We will just return the DateTime right away.
        if ($value instanceof DateTimeInterface) {
            return new Carbon(
                $value->format('Y-m-d H:i:s.u'),
                $value->getTimezone()
            );
        }

        // If this value is an integer, we will assume it is a UNIX timestamp's value
        // and format a Carbon object from this timestamp. This allows flexibility
        // when defining your date fields as they might be UNIX timestamps here.
        if (is_numeric($value)) {
            return Carbon::createFromTimestamp($value);
        }

        // If the value is in simply year, month, day format, we will instantiate the
        // Carbon instances from that format. Again, this provides for simple date
        // fields on the database, while still supporting Carbonized conversion.
        if ($this->isStandardDateFormat($value)) {
            return Carbon::createFromFormat($this->format, $value)->startOfDay();
        }

        // Finally, we will just assume this date is in the format used by default on
        // the database connection and use that format to create the Carbon object
        // that is returned back out to the developers after we convert it here.
        return Carbon::createFromFormat(
            str_replace('.v', '.u', $this->format),
            $value
        );
    }

    protected function isStandardDateFormat($value)
    {
        return preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $value);
    }
}
