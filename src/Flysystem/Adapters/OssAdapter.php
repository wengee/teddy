<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-07-15 23:04:55 +0800
 */

namespace Teddy\Flysystem\Adapters;

use League\Flysystem\Config;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\FilesystemException;
use League\Flysystem\PathPrefixer;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\UnableToWriteFile;
use League\Flysystem\Visibility;
use OSS\Core\OssException;
use OSS\OssClient;

class OssAdapter implements FilesystemAdapter
{
    /** @var OssClient */
    protected $client;

    /** @var string */
    protected $bucket;

    /** @var string */
    protected $endpoint = 'oss-cn-hangzhou.aliyuncs.com';

    /** @var string */
    protected $cdnDomain;

    /** @var bool */
    protected $ssl = false;

    /** @var bool */
    protected $isCName = false;

    protected PathPrefixer $prefixer;

    /** @var array */
    protected $options = [
        'Multipart' => 128,
    ];

    public function __construct(array $config)
    {
        $this->bucket    = $config['bucket'] ?? '';
        $this->endpoint  = $config['endpoint'] ?? $this->endpoint;
        $this->cdnDomain = $config['cdnDomain'] ?? '';
        $this->ssl       = $config['ssl'] ?? false;
        $this->isCName   = $config['isCName'] ?? false;

        $accessKeyId     = $config['accessKeyId'] ?? '';
        $accessKeySecret = $config['accessKeySecret'] ?? '';
        $securityToken   = $config['securityToken'] ?? null;
        $this->client    = new OssClient($accessKeyId, $accessKeySecret, $this->endpoint, $this->isCName, $securityToken);
        if ($this->ssl) {
            $this->client->setUseSSL(true);
        }

        $this->client->setTimeout($config['timeout'] ?? 600);
        $this->client->setConnectTimeout($config['connectTimeout'] ?? 10);

        $this->prefixer = new PathPrefixer($config['prefix'] ?? '', DIRECTORY_SEPARATOR);
    }

    /**
     * @throws FilesystemException
     */
    public function fileExists(string $path): bool
    {
        $prefixedPath = $this->prefixer->prefixPath($path);

        try {
            $ret = $this->client->doesObjectExist($this->bucket, $prefixedPath);
        } catch (OssException $e) {
            return false;
        }

        return $ret;
    }

    /**
     * @throws UnableToWriteFile
     * @throws FilesystemException
     */
    public function write(string $path, string $contents, Config $config): void
    {
        $prefixedPath = $this->prefixer->prefixPath($path);

        try {
            $this->client->putObject($this->bucket, $prefixedPath, $contents, $this->getOptions($config));
        } catch (OssException $e) {
            throw $e;
            // throw new FilesystemException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param resource $contents
     *
     * @throws UnableToWriteFile
     * @throws FilesystemException
     */
    public function writeStream(string $path, $contents, Config $config): void
    {
        $this->write($path, \stream_get_contents($contents), $config);
    }

    /**
     * @throws UnableToReadFile
     * @throws FilesystemException
     */
    public function read(string $path): string
    {
        $prefixedPath = $this->prefixer->prefixPath($path);

        return $this->client->getObject($this->bucket, $prefixedPath);
    }

    /**
     * @throws UnableToReadFile
     * @throws FilesystemException
     *
     * @return resource
     */
    public function readStream(string $path)
    {
        $prefixedPath = $this->prefixer->prefixPath($path);
        $contents     = $this->client->getObject($this->bucket, $prefixedPath);

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $contents);
        rewind($stream);

        return $stream;
    }

    /**
     * @throws UnableToDeleteFile
     * @throws FilesystemException
     */
    public function delete(string $path): void
    {
        $prefixedPath = $this->prefixer->prefixPath($path);
        $this->client->deleteObject($this->bucket, $prefixedPath);
    }

    /**
     * @throws UnableToDeleteDirectory
     * @throws FilesystemException
     */
    public function deleteDirectory(string $path): void
    {
        $prefixedPath = $this->prefixer->prefixPath($path);

        $nextMarker = '';
        while (true) {
            $options = [
                'max-keys'  => 500,
                'prefix'    => ('' === $prefixedPath) ? '' : (rtrim($prefixedPath, '/').'/'),
                'delimiter' => '/',
                'marker'    => $nextMarker,
            ];

            $res = $this->client->listObjects($this->bucket, $options);

            $nextMarker = $res->getNextMarker();
            $prefixList = $res->getPrefixList(); // 目录列表
            $objectList = $res->getObjectList(); // 文件列表

            if ($objectList) {
                $objects = array_map(function ($item) {
                    return $item->getKey();
                }, $objectList);

                $this->client->deleteObjects($this->bucket, $objects);
            }

            if ($prefixList) {
                foreach ($prefixList as $value) {
                    $dirPath = $this->prefixer->stripPrefix($value->getPrefix());
                    $this->deleteDirectory($dirPath);
                }
            }

            if ('' === $nextMarker) {
                break;
            }
        }

        $this->client->deleteObject($this->bucket, $prefixedPath);
    }

    /**
     * @throws UnableToCreateDirectory
     * @throws FilesystemException
     */
    public function createDirectory(string $path, Config $config): void
    {
        $dirname = $this->prefixer->prefixDirectoryPath($path);

        $this->client->createObjectDir($this->bucket, $dirname);
    }

    /**
     * @throws InvalidVisibilityProvided
     * @throws FilesystemException
     */
    public function setVisibility(string $path, string $visibility): void
    {
        $prefixedPath = $this->prefixer->prefixPath($path);
        $this->client->putObjectAcl(
            $this->bucket,
            $prefixedPath,
            (Visibility::PUBLIC === $visibility) ? 'public-read' : 'private'
        );
    }

    /**
     * @throws UnableToRetrieveMetadata
     * @throws FilesystemException
     */
    public function visibility(string $path): FileAttributes
    {
        $prefixedPath = $this->prefixer->prefixPath($path);
        $response     = $this->client->getObjectAcl($this->bucket, $prefixedPath);

        return new FileAttributes(
            $path,
            null,
            ('private' === $response) ? Visibility::PRIVATE : Visibility::PUBLIC
        );
    }

    /**
     * @throws UnableToRetrieveMetadata
     * @throws FilesystemException
     */
    public function mimeType(string $path): FileAttributes
    {
        $meta = $this->getMetadata($path);
        if (!$meta || null === $meta->mimeType()) {
            throw UnableToRetrieveMetadata::mimeType($path);
        }

        return $meta;
    }

    /**
     * @throws UnableToRetrieveMetadata
     * @throws FilesystemException
     */
    public function lastModified(string $path): FileAttributes
    {
        $meta = $this->getMetadata($path);
        if (!$meta || null === $meta->lastModified()) {
            throw UnableToRetrieveMetadata::lastModified($path);
        }

        return $meta;
    }

    /**
     * @throws UnableToRetrieveMetadata
     * @throws FilesystemException
     */
    public function fileSize(string $path): FileAttributes
    {
        $meta = $this->getMetadata($path);
        if (!$meta || null === $meta->fileSize()) {
            throw UnableToRetrieveMetadata::fileSize($path);
        }

        return $meta;
    }

    /**
     * @throws FilesystemException
     *
     * @return iterable<StorageAttributes>
     */
    public function listContents(string $path, bool $deep): iterable
    {
        $prefixedPath = $this->prefixer->prefixPath($path);

        $nextMarker = '';
        while (true) {
            $options = [
                'max-keys'  => 500,
                'prefix'    => ('' === $prefixedPath) ? '' : ($prefixedPath.'/'),
                'delimiter' => '/',
                'marker'    => $nextMarker,
            ];

            $res = $this->client->listObjects($this->bucket, $options);

            $nextMarker = $res->getNextMarker();
            $prefixList = $res->getPrefixList(); // 目录列表
            $objectList = $res->getObjectList(); // 文件列表

            if ($prefixList) {
                foreach ($prefixList as $value) {
                    $dirPath = $this->prefixer->stripPrefix($value->getPrefix());

                    yield new DirectoryAttributes($dirPath);

                    if ($deep) {
                        yield from $this->listContents($dirPath, $deep);
                    }
                }
            }

            if ($objectList) {
                foreach ($objectList as $value) {
                    if ((0 === $value->getSize()) && ($value->getKey() === $prefixedPath.'/')) {
                        continue;
                    }

                    $filePath = $this->prefixer->stripPrefix($value->getKey());

                    yield new FileAttributes(
                        $filePath,
                        (int) $value->getSize(),
                        null,
                        strtotime($value->getLastModified())
                    );
                }
            }

            if ('' === $nextMarker) {
                break;
            }
        }
    }

    /**
     * @throws UnableToMoveFile
     * @throws FilesystemException
     */
    public function move(string $source, string $destination, Config $config): void
    {
        $this->copy($source, $destination, $config);
        $this->delete($source);
    }

    /**
     * @throws UnableToCopyFile
     * @throws FilesystemException
     */
    public function copy(string $source, string $destination, Config $config): void
    {
        $prefixedSource      = $this->prefixer->prefixPath($source);
        $prefixedDestination = $this->prefixer->prefixPath($destination);

        $this->client->copyObject($this->bucket, $prefixedSource, $this->bucket, $prefixedDestination);
    }

    public function getUrl(string $path): string
    {
        $prefixedPath = $this->prefixer->prefixPath($path);

        return ($this->ssl ? 'https://' : 'http://').($this->cdnDomain ?: ($this->isCName ? $this->endpoint : ($this->bucket.'.'.$this->endpoint))).'/'.ltrim($prefixedPath, '/');
    }

    public function getMetadata($path): ?FileAttributes
    {
        $prefixedPath = $this->prefixer->prefixPath($path);

        $meta = $this->client->getObjectMeta($this->bucket, $prefixedPath);
        if (empty($meta)) {
            return null;
        }

        return new FileAttributes(
            $path,
            (int) $meta['content-length'] ?? 0,
            null,
            strtotime($meta['last-modified'] ?? ''),
            $meta['content-type'] ?? null,
        );
    }

    protected function getOptions(Config $config): array
    {
        $options = [];
        if ($headers = $config->get('headers')) {
            $options['headers'] = $headers;
        }

        if ($contentType = $config->get('Content-Type')) {
            $options['Content-Type'] = $contentType;
        }

        if ($contentMd5 = $config->get('Content-Md5')) {
            $options['Content-Md5'] = $contentMd5;
            $options['checkmd5']    = false;
        }

        return $options;
    }
}
