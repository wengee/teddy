<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-10-08 15:32:53 +0800
 */

namespace Teddy;

use ArrayIterator;
use Phar;

class PharBuilder
{
    protected $phar;

    protected $basePath;

    protected $options;

    public function __construct(string $basePath, array $extraOptions = [])
    {
        $this->basePath = $basePath;
        $this->options = (new Options([
            'dist'          => $this->joinPaths($basePath, 'dist'),
            'main'          => 'index.php',
            'output'        => 'app.phar',
            'directories'   => [],
            'files'         => [],
            'rules'         => [],
            'exclude'       => [],
            'shebang'       => true,
            'clear'         => false,
            'copy'          => [],
            'compress'      => 'none',
            'extensions'    => [],
        ]))->update($extraOptions);
    }

    public static function build(string $basePath, array $extraOptions = [])
    {
        return (new self($basePath, $extraOptions))->run();
    }

    public function run()
    {
        if (!$this->options['directories'] || !$this->options['files']) {
            return false;
        }

        if (!file_exists($this->options['dist'])) {
            mkdir($this->options['dist'], 0755);
        }

        if ($this->options['clear']) {
            Utils::clearDir($this->options['dist']);
        }

        $this->copyFiles();
        $s = microtime(true);
        $pharFile = $this->joinPaths($this->options['dist'], $this->options['output']);
        if (file_exists($pharFile)) {
            @unlink($pharFile);
        }

        $this->phar = new Phar($pharFile, 0, $this->options['output']);
        $this->phar->startBuffering();

        $this->compressFiles();
        $total = $this->addFiles();
        $this->setStub();

        $this->phar->stopBuffering();
        $elapsed = sprintf('%.3f', microtime(true) - $s);
        $filesize = Utils::humanFilesize(filesize($pharFile));
        chmod($pharFile, 0755);
        echo "Finished {$pharFile}, Size: {$filesize}, Total files: {$total}, Elapsed time: {$elapsed}s\n";
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
            $pos = strrpos($realpath, '.');
            $ext = ($pos === false) ? false : substr($realpath, $pos + 1);
            if ($ext === 'php' || in_array($ext, $this->options['extensions'])) {
                $files[$path] = $realpath;
            }
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

    protected function compressFiles(): void
    {
        switch ($this->options['compress']) {
            case 'gz':
            case 'gzip':
                $this->phar->compressFiles(Phar::GZ);
                break;

            case 'bz2':
            case 'bzip2':
                $this->phar->compressFiles(Phar::BZ2);
                break;

            default:
                break;
        }
    }

    protected function setStub(): void
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

    protected function copyFiles(): void
    {
        if (empty($this->options['copy'])) {
            return;
        }

        $files = (array) $this->options['copy'];
        foreach ($files as $key => $value) {
            $dest = $this->joinPaths($this->options['dist'], $value);
            if (is_int($key)) {
                $source = $this->joinPaths($this->basePath, $value);
            } else {
                $source = $this->joinPaths($this->basePath, $key);
            }

            Utils::xcopy($source, $dest);
        }
    }

    protected function setDist(string $value): void
    {
        if (!is_dir($value)) {
            mkdir($value, 0755);
        }

        $this->options['dist'] = $value;
    }
}
