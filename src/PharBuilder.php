<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-04-15 11:36:56 +0800
 */
namespace Teddy;

use ArrayIterator;
use Phar;
use Teddy\Traits\HasOptions;

class PharBuilder
{
    use HasOptions;

    protected $phar;

    protected $basePath;

    protected $options;

    public function __construct(string $basePath, array $extraOptions = [])
    {
        $this->basePath = $basePath;
        $options = $this->loadJsonOptions();
        $options += $this->getDefaultOptions();
        $options = array_merge($options, $extraOptions);
        $this->hydrate($options);
    }

    public static function build(string $basePath, array $extraOptions = [])
    {
        return (new self($basePath, $extraOptions))->run();
    }

    public function run()
    {
        if (empty($this->options['directories']) || empty($this->options['files'])) {
            return false;
        }

        $s = microtime(true);
        $pharFile = $this->joinPaths($this->options['dist'], $this->options['output']);
        if (file_exists($pharFile)) {
            @unlink($pharFile);
        }

        $this->phar = new Phar($pharFile, 0, $this->options['output']);
        $this->phar->startBuffering();

        $total = $this->addFiles();
        $this->setStub();
        $this->phar->stopBuffering();
        $elapsed = sprintf('%.3f', microtime(true) - $s);

        file_put_contents($pharFile . '.md5sum', md5_file($pharFile));
        chmod($pharFile, 0755);
        echo "Finished {$pharFile}, Total files: {$total}, Elapsed time: {$elapsed}s\n";
    }

    protected function addFiles()
    {
        $files = [];
        foreach ($this->options['directories'] as $dir) {
            $this->addFile($dir, $files);
        }

        foreach ($this->options['files'] as $file) {
            $this->addFile($file, $files);
        }

        $files = new ArrayIterator($files);
        $this->phar->buildFromIterator($files);
        return $files->count();
    }

    protected function addFile(string $path, array &$files = [])
    {
        if (!$this->checkPath($path)) {
            return false;
        }

        $realpath = $this->joinPaths($this->basePath, $path);
        if (is_file($realpath)) {
            $files[$path] = $realpath;
        } elseif (is_dir($realpath)) {
            if ($dh = opendir($realpath)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file === '.' || $file === '..') {
                        continue;
                    }

                    $subPath = $this->joinPaths($path, $file);
                    $this->addFile($subPath, $files);
                }

                closedir($dh);
            }
        }
    }

    protected function setStub()
    {
        if (is_string($this->options['main'])) {
            $stub = $this->phar->createDefaultStub($this->options['main']);
        } elseif (is_array($this->options['main'])) {
            $args = array_values($this->options['main']);
            $stub = $this->phar->createDefaultStub(...$args);
        } else {
            $stub = $this->phar->createDefaultStub('index.php');
        }

        if ($this->options['shebang']) {
            $stub = "#!/usr/bin/env php\n" . $stub;
        }

        $this->phar->setStub($stub);
    }

    protected function checkPath(string $path)
    {
        if ($this->checkExclude($path)) {
            return false;
        }

        return $this->checkRule($path);
    }

    protected function checkRule(string $path)
    {
        if (empty($this->options['rules'])) {
            return true;
        }

        $rules = (array) $this->options['rules'];
        foreach ($rules as $rule) {
            if (preg_match('#' . $rule . '#i', $path)) {
                return true;
            }
        }

        return false;
    }

    protected function checkExclude(string $path)
    {
        if (empty($this->options['exclude'])) {
            return false;
        }

        $rules = (array) $this->options['exclude'];
        foreach ($rules as $rule) {
            if (preg_match('#' . $rule . '#i', $path)) {
                return true;
            }
        }

        return false;
    }

    protected function loadJsonOptions(): array
    {
        $jsonFile = $this->joinPaths($this->basePath, 'build.json');
        if (is_file($jsonFile)) {
            $options = @\json_decode(\file_get_contents($jsonFile), true);
            if ($options && is_array($options)) {
                return $options;
            }
        }

        return [];
    }

    protected function getDefaultOptions(): array
    {
        return [
            'dist' => $this->joinPaths($this->basePath, 'dist'),
            'main' => 'index.php',
            'output' => 'app.phar',
            'directories' => [],
            'files' => [],
            'rules' => [],
            'exclude' => [],
            'shebang' => true,
        ];
    }

    protected function joinPaths(...$args): string
    {
        $firstArg = '';
        if (isset($args[0])) {
            $firstArg = rtrim($args[0], '\\/') . DIRECTORY_SEPARATOR;
            unset($args[0]);
        }

        $paths = array_map(function ($p) {
            return trim($p, '\\/');
        }, $args);
        $paths = array_filter($paths);
        return $firstArg . implode(DIRECTORY_SEPARATOR, $paths);
    }

    protected function setDist(string $value)
    {
        if (!is_dir($value)) {
            mkdir($value, 0755);
        }

        $this->options['dist'] = $value;
    }
}
