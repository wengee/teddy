<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-14 14:53:45 +0800
 */

namespace Teddy\Model;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Exception;

class Manager
{
    private $reader;

    private $metaInfos = [];

    public function __construct()
    {
        $this->registerLoader();
        $this->reader = new AnnotationReader;
    }

    public function metaInfo($model): ?MetaInfo
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
                $this->metaInfos[$model] = null;
            }
        }

        return $this->metaInfos[$model];
    }

    protected function registerLoader()
    {
        if (!class_exists('\\Composer\\Autoload\\ClassLoader')) {
            return false;
        }

        $loaders = spl_autoload_functions();
        foreach ($loaders as $loader) {
            if (!is_array($loader)) {
                continue;
            } elseif (isset($loader[0]) && ($loader[0] instanceof \Composer\Autoload\ClassLoader)) {
                AnnotationRegistry::registerLoader($loader);
                return true;
            }
        }

        return false;
    }
}
