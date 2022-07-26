<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-07-26 10:44:59 +0800
 */

namespace Teddy\Config;

use Dotenv\Dotenv;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use JsonSerializable;
use Symfony\Component\Yaml\Tag\TaggedValue;
use Symfony\Component\Yaml\Yaml;
use Teddy\Interfaces\ConfigTagInterface;
use Teddy\Interfaces\ContainerAwareInterface;
use Teddy\Interfaces\ContainerInterface;
use Teddy\Interfaces\WithContainerInterface;
use Teddy\Traits\ContainerAwareTrait;
use Teddy\Utils\FileSystem;

class Config extends Repository implements WithContainerInterface, ContainerAwareInterface, JsonSerializable
{
    use ContainerAwareTrait;

    protected static $tags = [
        'env'  => Tags\EnvTag::class,
        'eval' => Tags\EvalTag::class,
    ];

    /** @var string[] */
    protected array $configDirs = [];

    /** @var string[] */
    protected array $configFiles = [];

    protected ?string $dotEnvDir;

    protected bool $freezed = false;

    protected array $freezedData = [];

    protected array $cached = [];

    protected ?string $basePath;

    protected ?string $runtimePath;

    public function __construct(ContainerInterface $container)
    {
        $this->container    = $container;
        $this->configDirs[] = dirname(__DIR__).'/_config';

        $basePath = $container->get('basePath');
        if ($basePath) {
            $this->basePath = $basePath;

            $this->configDirs[]  = FileSystem::joinPath($basePath, 'config');
            $this->configFiles[] = FileSystem::joinPath($basePath, 'config.yml');
            $this->configFiles[] = FileSystem::joinPath($basePath, 'config.yaml');

            $this->dotEnvDir = $basePath;
        }

        $runtimePath = Filesystem::getRuntimePath();
        if ($runtimePath) {
            $this->runtimePath = $runtimePath;

            $this->configFiles[] = FileSystem::joinPath($runtimePath, 'config.yml');
            $this->configFiles[] = FileSystem::joinPath($runtimePath, 'config.yaml');

            $this->dotEnvDir = $runtimePath;
        }

        $this->initialize();
    }

    public static function addTag(string $name, ConfigTagInterface $definition): void
    {
        self::$tags[$name] = $definition;
    }

    public function get(string $key, $default = null)
    {
        return Arr::get($this->freezedData, $key, $default);
    }

    public function has(string $key): bool
    {
        return Arr::has($this->freezedData, $key);
    }

    public function all(): array
    {
        return $this->freezedData;
    }

    public function jsonSerialize(): mixed
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
        $content = file_get_contents($file);
        $content = strtr($content, [
            '__DIR__'      => dirname($file),
            '__CWD__'      => getcwd(),
            'BASE_PATH'    => $this->basePath,
            'RUNTIME_PATH' => $this->runtimePath ?: $this->basePath,
        ]);

        $config = Yaml::parse($content, Yaml::PARSE_CUSTOM_TAGS);
        if ($config && is_array($config)) {
            $config = $this->parseValue($config);

            $this->merge($config, true);
        }
    }

    private function parseValue(array $data): array
    {
        return array_map(function ($item) {
            if (is_array($item)) {
                return $this->parseValue($item);
            }

            if ($item instanceof TaggedValue) {
                $tagName = $item->getTag();
                $value   = $item->getValue();

                return $this->parseTagValue($tagName, $value);
            }

            return $item;
        }, $data);
    }

    private function parseTagValue(string $tag, $value)
    {
        $definition = self::$tags[$tag] ?? null;
        if ($definition) {
            /** @var ConfigTagInterface */
            $definition = is_string($definition) ? new $definition() : $definition;

            return $definition->parseValue($value);
        }

        return $value;
    }
}
