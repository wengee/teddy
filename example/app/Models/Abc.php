<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-01-26 17:25:36 +0800
 */

namespace App\Models;

use Teddy\Model\Columns\DateTimeColumn;
use Teddy\Model\Columns\IdColumn;
use Teddy\Model\Columns\TimestampColumn;
use Teddy\Model\Model;

#[IdColumn()]
#[DateTimeColumn("timeslot", default: "now")]
#[TimestampColumn("created", default: "now")]
#[TimestampColumn("updated", update: true)]
class Abc extends Model
{
}
