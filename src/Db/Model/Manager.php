<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-04-10 18:43:40 +0800
 */
namespace Teddy\Db\Model;

use Doctrine\Common\Annotations\AnnotationReader;
use Exception;

class Manager
{
    private $reader;

    private $metaInfos = [];

    public function __construct()
    {
        $this->reader = new AnnotationReader;
    }

    public function metaInfo($model)
    {
        if (is_object($model)) {
            $model = get_class($model);
        } else {
            $model = (string) $model;
        }

        if (!isset($this->metaInfos[$model])) {
            try {
                $this->metaInfos[$model] = new MetaInfo($model);
            } catch (Exception $e) {
                log_exception($e);
                $this->metaInfos[$model] = false;
            }
        }

        return $this->metaInfos[$model];
    }
}
