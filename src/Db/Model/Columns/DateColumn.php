<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-01 17:42:26 +0800
 */
namespace SlimExtra\Db\Model\Columns;

/**
 * @Annotation
 * @Target("CLASS")
 */
class DateColumn extends DateTimeColumn
{
    protected $format = 'Y-m-d';
}
