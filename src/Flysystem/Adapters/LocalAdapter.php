<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-08-07 22:26:10 +0800
 */

namespace Teddy\Flysystem\Adapters;

use League\Flysystem\Config;
use League\Flysystem\FilesystemException;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\PathPrefixer;
use League\Flysystem\UnableToWriteFile;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use League\Flysystem\UnixVisibility\VisibilityConverter;
use League\Flysystem\Visibility;
use Teddy\Interfaces\FilesystemAdapter;

class LocalAdapter extends LocalFilesystemAdapter implements FilesystemAdapter
{
    protected string $urlPrefix = '';

    private PathPrefixer $prefixer;

    private VisibilityConverter $visibility;

    public function __construct(array $config)
    {
        $this->urlPrefix = rtrim($config['url'] ?? '', '/');

        $location       = $config['location'] ?? ($config['root'] ?? '');
        $this->prefixer = new PathPrefixer($location, DIRECTORY_SEPARATOR);

        $directoryVisibility = $config['directoryVisibility'] ?? $config['directory_visibility'] ?? Visibility::PUBLIC;
        $visibility          = new PortableVisibilityConverter(0644, 0600, 0755, 0700, $directoryVisibility);
        $this->visibility    = $visibility;

        parent::__construct($location, $visibility);
    }

    /**
     * @throws UnableToWriteFile
     * @throws FilesystemException
     */
    public function append(string $path, string $contents, Config $config): void
    {
        $this->appendToFile($path, $contents, $config);
    }

    /**
     * @param resource $contents
     *
     * @throws UnableToWriteFile
     * @throws FilesystemException
     */
    public function appendStream(string $path, $contents, Config $config): void
    {
        $this->appendToFile($path, \stream_get_contents($contents), $config);
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

    private function appendToFile(string $path, string $contents, Config $config): void
    {
        $prefixedLocation = $this->prefixer->prefixPath($path);
        $this->ensureDirectoryExists(
            dirname($prefixedLocation),
            $this->resolveDirectoryVisibility($config->get(Config::OPTION_DIRECTORY_VISIBILITY))
        );
        error_clear_last();

        if (!($fp = fopen($prefixedLocation, 'a+'))) {
            throw UnableToWriteFile::atLocation($path, 'Cannot open file');
        }

        if (false === fwrite($fp, $contents)) {
            throw UnableToWriteFile::atLocation($path, 'Cannot write to file');
        }

        fclose($fp);

        if ($visibility = $config->get(Config::OPTION_VISIBILITY)) {
            $this->setVisibility($path, (string) $visibility);
        }
    }
}
