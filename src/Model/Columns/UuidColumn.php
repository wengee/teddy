<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-01-20 11:18:01 +0800
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
