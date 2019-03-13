<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-13 16:45:24 +0800
 */
namespace Teddy\Providers;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Teddy\Db\Database;
use Teddy\Db\Model\Manager as ModelManager;
use Teddy\Swoole\Db\Database as SwooleDatabase;

class DbProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $settings = $container['settings']->get('database');
        if ($settings) {
            $this->registerLoader();

            $settings = (array) $settings;
            $container['modelManager'] =  new ModelManager;

            if (defined('IN_SWOOLE') && IN_SWOOLE) {
                $container['db'] = new SwooleDatabase($settings);
            } else {
                $container['db'] = new Database($settings);
            }
        }
    }

    protected function registerLoader()
    {
        if (!\class_exists('\\Composer\\Autoload\\ClassLoader')) {
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
