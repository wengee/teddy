<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-08-31 16:49:50 +0800
 */

namespace Teddy\Config;

use Dotenv\Dotenv;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use JsonSerializable;
use Symfony\Component\Yaml\Yaml;
use Teddy\Utils\FileSystem;

class Config extends Repository implements JsonSerializable
{
    /** @var string[] */
    protected $configDirs = [];

    /** @var string[] */
    protected $configFiles = [];

    /** @var null|string */
    protected $dotEnvDir;

    /** @var bool */
    protected $freezed = false;

    /** @var array */
    protected $freezedData = [];

    /** @var array */
    protected $cached = [];

    public function __construct(?string $basePath = null)
    {
        $this->configDirs[] = dirname(__DIR__).'/_config';
        if ($basePath) {
            $this->configDirs[]  = FileSystem::joinPath($basePath, 'config');
            $this->configFiles[] = FileSystem::joinPath($basePath, 'config.yml');
            $this->configFiles[] = FileSystem::joinPath($basePath, 'config.yaml');

            $this->dotEnvDir = $basePath;
        }

        $runtimePath  = Filesystem::getRuntimePath();
        if ($runtimePath) {
            $this->configFiles[] = FileSystem::joinPath($runtimePath, 'config.yml');
            $this->configFiles[] = FileSystem::joinPath($runtimePath, 'config.yaml');

            $this->dotEnvDir = $runtimePath;
        }

        $this->initialize();
    }

    public function get(string $key, $default = null)
    {
        return Arr::get($this->freezedData, $key, $default);
    }

    public function has(string $key): bool
    {
        return Arr::has($this->freezedData, $key);
    }

    public function jsonSerialize()
    {
        if (!$this->freezed) {
            $this->freeze();
        }

        return $this->freezedData;
    }

    private function freeze(): void
    {
        if (!$this->freezed) {
            $this->freezedData = $this->toArray();
            $this->freezed     = true;
        }
    }

    private function initialize(): void
    {
        try {
            Dotenv::createMutable($this->dotEnvDir, '.env')->load();
        } catch (Exception $e) {
        }

        foreach ($this->configDirs as $dir) {
            if (is_dir($dir)) {
                $items = $this->loadConfigDir($dir);
                $this->merge($items);
            }
        }

        foreach ($this->configFiles as $file) {
            if (is_file($file)) {
                $this->loadYamlConfig($file);
            }
        }

        $this->freeze();
    }

    private function loadConfigDir(string $dir): array
    {
        $items  = [];
        $handle = opendir($dir);
        while (false !== ($file = readdir($handle))) {
            $filepath = FileSystem::joinPath($dir, $file);
            if (Str::endsWith($file, '.php') && is_file($filepath)) {
                $key    = substr($file, 0, -4);
                $config = require $filepath;
                if (is_array($config)) {
                    $config = new Repository($config);
                }

                $items[$key] = $config;
            }
        }
        closedir($handle);

        return $items;
    }

    private function loadYamlConfig(string $file): void
    {
        $config = Yaml::parseFile($file);
        if ($config && is_array($config)) {
            $this->merge($config, true);
        }
    }
}
