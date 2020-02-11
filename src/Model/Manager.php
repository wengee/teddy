<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-02-11 20:55:53 +0800
 */

namespace Teddy\Model;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Exception;
use Teddy\Utils\Composer;

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
        $loader = Composer::getLoader();
        AnnotationRegistry::registerLoader([$loader, 'loadClass']);
    }
}
