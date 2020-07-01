<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-07-01 10:46:19 +0800
 */

namespace Teddy\Model\Columns;

use Ramsey\Uuid\Uuid;

/**
 * @Annotation
 * @Target("CLASS")
 */
class UuidColumn extends Column
{
    protected $version = 4;

    public function dbValue($value)
    {
        if (!$value) {
            return $this->generateId();
        }

        return (string) $value;
    }

    public function value($value)
    {
        return (string) $value;
    }

    public function defaultValue()
    {
        return $this->generateId();
    }

    protected function generateId(): String
    {
        switch ($this->version) {
            case 1:
                $id = Uuid::uuid1();
                break;

            default:
                $id = Uuid::uuid4();
        }

        return (string) $id;
    }
}
