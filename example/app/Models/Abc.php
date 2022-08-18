<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-18 17:10:22 +0800
 */

namespace App\Models;

use Teddy\Model\Columns\IdColumn;
use Teddy\Model\Columns\TimestampColumn;
use Teddy\Model\Model;
use Teddy\Model\Table;

#[Table(suffixed: 'abc_{SUFFIX}')]
#[IdColumn()]
#[TimestampColumn('created', default: 'now')]
#[TimestampColumn('updated', update: true)]
class Abc extends Model
{
}
