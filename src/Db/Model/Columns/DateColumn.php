<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-01 17:42:26 +0800
 */
namespace Teddy\Db\Model\Columns;

/**
 * @Annotation
 * @Target("CLASS")
 */
class DateColumn extends DateTimeColumn
{
    protected $format = 'Y-m-d';
}
