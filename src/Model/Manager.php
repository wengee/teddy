<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-26 11:40:12 +0800
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

    protected function registerLoader(): void
    {
        $loaderFile = vendor_path('autoload.php');
        if ($loaderFile && is_file($loaderFile)) {
            $loader = require $loaderFile;
            AnnotationRegistry::registerLoader([$loader, 'loadClass']);
        }
    }
}
