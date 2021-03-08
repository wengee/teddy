<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-03-08 10:40:51 +0800
 */

namespace App\Models;

use Teddy\Model\Columns\BooleanColumn;
use Teddy\Model\Columns\IdColumn;
use Teddy\Model\Columns\IntegerColumn;
use Teddy\Model\Columns\JsonColumn;
use Teddy\Model\Columns\StringColumn;
use Teddy\Model\Columns\TimestampColumn;
use Teddy\Model\Model;
use Teddy\Model\Table;

/**
 * @Table("attachment")
 * @IdColumn
 * @StringColumn("openId", field="open_id")
 * @StringColumn("path", default="aa")
 * @StringColumn("name")
 * @StringColumn("ext")
 * @IntegerColumn("size")
 * @StringColumn("mimetype")
 * @BooleanColumn("isImage", field="is_image")
 * @IntegerColumn("width")
 * @IntegerColumn("height")
 * @StringColumn("url")
 * @JsonColumn("extra")
 * @TimestampColumn("created", default="now")
 * @TimestampColumn("updated", update=true)
 */
class Attachment extends Model
{
}
