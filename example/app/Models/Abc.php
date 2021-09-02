<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-02 14:32:18 +0800
 */

namespace App\Models;

use Teddy\Model\Columns\IdColumn;
use Teddy\Model\Columns\TimestampColumn;
use Teddy\Model\Model;

/**
 * @IdColumn
 * @TimestampColumn("created", default="now")
 * @TimestampColumn("updated", update=true)
 */
class Abc extends Model
{
}
