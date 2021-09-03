<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
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

    public function convertToDbValue($value)
    {
        if (!$value && $this->primaryKey) {
            return $this->generateId();
        }

        return (string) $value;
    }

    public function convertToPhpValue($value)
    {
        return (string) $value;
    }

    public function defaultValue()
    {
        return $this->primaryKey ? $this->generateId() : $this->default;
    }

    protected function generateId(): string
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
