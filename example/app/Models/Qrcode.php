<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-15 10:31:53 +0800
 */

namespace App\Models;

use Teddy\Model\Columns\DateTimeColumn;
use Teddy\Model\Columns\IntegerColumn;
use Teddy\Model\Columns\PrimaryKeyColumn;
use Teddy\Model\Columns\StringColumn;
use Teddy\Model\Model;
use Teddy\Model\Table;

/**
 * @Table("qrcode_table")
 * @PrimaryKeyColumn
 * @DateTimeColumn("created", field="creation_time")
 * @StringColumn("code")
 * @IntegerColumn("status")
 */
class Qrcode extends Model
{
}
