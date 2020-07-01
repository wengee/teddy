<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-07-01 10:47:54 +0800
 */

namespace App\Models;

use Teddy\Model\Columns\DateTimeColumn;
use Teddy\Model\Columns\IntegerColumn;
use Teddy\Model\Columns\StringColumn;
use Teddy\Model\Columns\UuidColumn;
use Teddy\Model\Model;
use Teddy\Model\Table;

/**
 * @Table("qrcode_table")
 * @UuidColumn("id")
 * @DateTimeColumn("created", field="creation_time")
 * @StringColumn("code", default="aa")
 * @IntegerColumn("status")
 */
class Qrcode extends Model
{
}
