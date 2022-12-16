<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-12-16 14:29:38 +0800
 */

namespace Teddy\Flysystem\Adapters;

use League\Flysystem\Config;
use League\Flysystem\FilesystemException;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\UnableToWriteFile;
use Teddy\Interfaces\FilesystemAdapter;

class LocalAdapter extends LocalFilesystemAdapter implements FilesystemAdapter
{
    /**
     * @var string
     */
    protected $urlPrefix = '';

    public function __construct(array $config)
    {
        $this->urlPrefix = rtrim($config['url'] ?? '', '/');
        $location        = $config['location'] ?? ($config['root'] ?? '');
        parent::__construct($location);
    }

    /**
     * @throws UnableToWriteFile
     * @throws FilesystemException
     */
    public function append(string $path, string $contents, int $position, Config $config): void
    {
        $this->appendToFile($path, $contents, $position, $config);
    }

    /**
     * @param resource $contents
     *
     * @throws UnableToWriteFile
     * @throws FilesystemException
     */
    public function appendStream(string $path, $contents, int $position, Config $config): void
    {
        $this->appendToFile($path, \stream_get_contents($contents), $position, $config);
    }

    public function getUrl(string $path): string
    {
        return $this->urlPrefix.'/'.ltrim($path, '/');
    }

    protected function resolveDirectoryVisibility(?string $visibility): int
    {
        return null === $visibility ? $this->visibility->defaultForDirectories() : $this->visibility->forDirectory(
            $visibility
        );
    }

    private function appendToFile(string $path, string $contents, int $position, Config $config): void
    {
        $prefixedLocation = $this->prefixer->prefixPath($path);
        $this->ensureDirectoryExists(
            dirname($prefixedLocation),
            $this->resolveDirectoryVisibility($config->get(Config::OPTION_DIRECTORY_VISIBILITY))
        );
        error_clear_last();

        if (!($fp = fopen($prefixedLocation, 'w+'))) {
            throw UnableToWriteFile::atLocation($path, 'Cannot open file');
        }

        fseek($fp, $position);
        if (false === fwrite($fp, $contents)) {
            throw UnableToWriteFile::atLocation($path, 'Cannot write to file');
        }

        fclose($fp);

        if ($visibility = $config->get(Config::OPTION_VISIBILITY)) {
            $this->setVisibility($path, (string) $visibility);
        }
    }
}
