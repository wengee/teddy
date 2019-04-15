<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-04-15 17:36:55 +0800
 */
namespace Teddy\Swoole;

use Teddy\Traits\HasOptions;

class Inotify
{
    use HasOptions;

    protected $basePath;

    protected $options = [];

    protected $inotify;

    protected $handler;

    protected $doing = false;

    protected $watchedDirs = [];

    protected $maskValue = IN_ATTRIB | IN_CREATE | IN_DELETE | IN_DELETE_SELF | IN_MODIFY | IN_MOVE;

    public function __construct(string $basePath, array $options = [])
    {
        $this->basePath = $basePath;

        $options += [
            'directories' => [],
            'exclude' => [],
            'suffixes' => ['.php', '.json'],
        ];

        $this->hydrate($options);
        $this->init();
    }

    public function __destruct()
    {
        $this->stop();
    }

    public function setHandler(callable $handler)
    {
        $this->handler = $handler;
    }

    public function init()
    {
        $this->inotify = inotify_init();
        return $this;
    }

    public function watch()
    {
        $this->watchDirectory();
        if (!empty($this->options['directories'])) {
            $dirs = (array) $this->options['directories'];
            foreach ($dirs as $dir) {
                $this->watchAllDirectories($dir);
            }
        }

        return $this;
    }

    public function start()
    {
        swoole_event_add($this->inotify, function ($inotify) {
            $events = inotify_read($inotify);
            foreach ($events as $event) {
                if ($event['mask'] === IN_IGNORED) {
                    continue;
                }

                if (empty($event['name']) || !$this->inWatchedSuffixes($event['name'])) {
                    continue;
                }

                if ($event['mask'] === IN_CREATE) {
                    $path = $this->getPath($event);
                    $realpath = $this->basePath . DIRECTORY_SEPARATOR . $path;
                    if (is_dir($realpath)) {
                        $this->watchDirectory($path, $realpath);
                    }
                } elseif ($event['mask'] === IN_DELETE || $event['mask'] === IN_DELETE_SELF) {
                    $path = $this->getPath($event);
                    $wd = array_search($path, $this->watchedDirs, true);
                    if (inotify_rm_watch($this->inotify, $wd)) {
                        unset($this->watchedDirs[$wd]);
                    }
                }

                if (!$this->doing) {
                    swoole_timer_after(500, function () use ($event) {
                        call_user_func_array($this->handler, [$event]);
                        $this->doing = false;
                    });

                    $this->doing = true;
                }
            }
        });
        // swoole_event_wait();
    }

    public function restart()
    {
        $this->stop();
        $this->init()->watch()->start();
    }

    public function stop()
    {
        swoole_event_del($this->inotify);
        swoole_event_exit();

        fclose($this->inotify);
        $this->watchedDirs = [];
    }

    protected function setMaskValue(int $value)
    {
        $this->maskValue = $value;
    }

    protected function isWatchedSuffix(string $name)
    {
        return true;
    }

    protected function watchAllDirectories(string $dir = '')
    {
        $realpath = $this->basePath . DIRECTORY_SEPARATOR . $dir;
        if (!is_dir($realpath)) {
            return false;
        }

        if (!empty($dir)) {
            if (!$this->watchDirectory($dir)) {
                return false;
            }
        }

        $names = scandir($realpath);
        foreach ($names as $name) {
            if (in_array($name, ['.', '..'], true)) {
                continue;
            }

            $subdir = $dir . DIRECTORY_SEPARATOR . $name;
            $this->watchAllDirectories($subdir);
        }
    }

    protected function watchDirectory(string $dir = '', ?string $realpath = null)
    {
        if ($this->isExcluded($dir)) {
            return false;
        }

        if (!$this->isWatched($dir)) {
            if ($realpath === null) {
                $realpath = $this->basePath . DIRECTORY_SEPARATOR . $dir;
            }

            $wd = inotify_add_watch($this->inotify, $realpath, $this->maskValue);
            if ($wd) {
                $this->watchedDirs[$wd] = $dir;
            }
        }

        return true;
    }

    protected function isExcluded(string $dir)
    {
        if (empty($this->options['exclude'])) {
            return false;
        }

        $excludeDirs = (array) $this->options['exclude'];
        return in_array($dir, $excludeDirs, true);
    }

    protected function isWatched(string $dir)
    {
        return in_array($dir, $this->watchedDirs, true);
    }

    protected function inWatchedSuffixes($file)
    {
        if (empty($this->options['suffixes'])) {
            return true;
        }

        $suffixes = (array) $this->options['suffixes'];
        foreach ($suffixes as $suffix) {
            $start = strlen($suffix);
            if (substr($file, -$start, $start) === $suffix) {
                return true;
            }
        }

        return false;
    }

    protected function getPath($event)
    {
        $wd = $event['wd'] ?? 0;
        $dir = array_get($this->watchedDirs, $wd);
        if (!$dir) {
            return null;
        }

        return $dir . DIRECTORY_SEPARATOR . $event['name'];
    }
}
