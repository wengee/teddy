<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-03-22 15:51:46 +0800
 */

namespace Teddy\Flysystem\Adapters;

use Exception;
use League\Flysystem\Config;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemException;
use League\Flysystem\InvalidVisibilityProvided;
use League\Flysystem\PathPrefixer;
use League\Flysystem\StorageAttributes;
use League\Flysystem\UnableToCopyFile;
use League\Flysystem\UnableToCreateDirectory;
use League\Flysystem\UnableToDeleteDirectory;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToMoveFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\UnableToWriteFile;
use League\Flysystem\Visibility;
use Qcloud\Cos\Client as CosClient;
use Teddy\Interfaces\FilesystemAdapter;

class CosAdapter implements FilesystemAdapter
{
    protected CosClient $client;

    protected string $schema = '';

    protected string $region = '';

    protected string $bucket = '';

    protected string $cdnDomain = '';

    protected PathPrefixer $prefixer;

    public function __construct(array $config)
    {
        $this->schema    = $config['schema'] ?? 'https';
        $this->region    = $config['region'] ?? 'ap-guangzhou';
        $this->bucket    = $config['bucket'] ?? '';
        $this->cdnDomain = $config['cdnDomain'] ?? '';

        $this->client   = new CosClient($config);
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
        } catch (Exception $e) {
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
            $this->client->putObject([
                'Bucket' => $this->bucket,
                'Key'    => $prefixedPath,
                'Body'   => $contents,
            ]);
        } catch (Exception $e) {
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
     * @throws UnableToWriteFile
     * @throws FilesystemException
     */
    public function append(string $path, string $contents, Config $config): void
    {
        if (!$this->fileExists($path)) {
            $this->write($path, $contents, $config);

            return;
        }

        $position     = (int) $this->fileSize($path)->fileSize();
        $prefixedPath = $this->prefixer->prefixPath($path);

        try {
            $this->client->appendObject([
                'Bucket'   => $this->bucket,
                'Key'      => $prefixedPath,
                'Position' => $position,
                'Body'     => $contents,
            ]);
        } catch (Exception $e) {
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
    public function appendStream(string $path, $contents, Config $config): void
    {
        $this->append($path, \stream_get_contents($contents), $config);
    }

    /**
     * @throws UnableToReadFile
     * @throws FilesystemException
     */
    public function read(string $path): string
    {
        $prefixedPath = $this->prefixer->prefixPath($path);
        $ret          = $this->client->getObject([
            'Bucket' => $this->bucket,
            'Key'    => $prefixedPath,
        ]);

        return $ret['Body']->getContents();
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
        $ret          = $this->client->getObject([
            'Bucket' => $this->bucket,
            'Key'    => $prefixedPath,
        ]);

        return $ret['Body']->detach();
    }

    /**
     * @throws UnableToDeleteFile
     * @throws FilesystemException
     */
    public function delete(string $path): void
    {
        $prefixedPath = $this->prefixer->prefixPath($path);
        $this->client->deleteObject([
            'Bucket' => $this->bucket,
            'Key'    => $prefixedPath,
        ]);
    }

    /**
     * @throws UnableToDeleteDirectory
     * @throws FilesystemException
     */
    public function deleteDirectory(string $path): void
    {
        $prefixedPath = $this->prefixer->prefixPath($path);
        $nextMarker   = '';
        $isTruncated  = true;

        while ($isTruncated) {
            $result = $this->client->ListObjects([
                'Bucket'       => $this->bucket,
                'Delimiter'    => '',
                'EncodingType' => 'url',
                'Marker'       => $nextMarker,
                'Prefix'       => $prefixedPath,
                'MaxKeys'      => 500,
            ]);

            $isTruncated = $result['IsTruncated'];
            $nextMarker  = $result['NextMarker'];
            foreach ($result['Contents'] as $content) {
                $this->client->deleteObject([
                    'Bucket' => $this->bucket,
                    'Key'    => $content['Key'],
                ]);
            }
        }
    }

    /**
     * @throws UnableToCreateDirectory
     * @throws FilesystemException
     */
    public function createDirectory(string $path, Config $config): void
    {
        $prefixedPath = $this->prefixer->prefixPath($path);

        $this->client->putObject([
            'Bucket' => $this->bucket,
            'Key'    => $prefixedPath.'/',
            'Body'   => '',
        ]);
    }

    /**
     * @throws InvalidVisibilityProvided
     * @throws FilesystemException
     */
    public function setVisibility(string $path, string $visibility): void
    {
        $this->client->putObjectAcl([
            'Bucket' => $this->bucket,
            'Key'    => $this->prefixer->prefixPath($path),
            'ACL'    => (Visibility::PUBLIC === $visibility) ? 'public-read' : 'private',
        ]);
    }

    /**
     * @throws UnableToRetrieveMetadata
     * @throws FilesystemException
     */
    public function visibility(string $path): FileAttributes
    {
        $prefixedPath = $this->prefixer->prefixPath($path);

        $result = $this->client->getObjectAcl([
            'Bucket' => $this->bucket,
            'Key'    => $prefixedPath,
        ]);

        $grants = $result['Grants'][0]['Grant'] ?? [];
        $grants = isset($grants[0]) ? $grants : [$grants];
        foreach ($grants as $grant) {
            if ('READ' === $grant['Permission'] && str_contains($grant['Grantee']['URI'] ?? '', 'global/AllUsers')) {
                return new FileAttributes($path, null, Visibility::PUBLIC);
            }
        }

        return new FileAttributes($path, null, Visibility::PRIVATE);
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
        $nextMarker   = '';
        $isTruncated  = true;

        while ($isTruncated) {
            $result = $this->client->listObjects([
                'Bucket'       => $this->bucket,
                'Delimiter'    => '/',
                'EncodingType' => 'url',
                'Marker'       => $nextMarker,
                'Prefix'       => ('' === $prefixedPath) ? '' : (rtrim($prefixedPath, '/').'/'),
                'MaxKeys'      => 500,
            ]);

            $isTruncated = $result['IsTruncated'];
            $nextMarker  = $result['NextMarker'];

            $commonPrefixes = $result['CommonPrefixes'] ?? [];
            if ($commonPrefixes) {
                foreach ($commonPrefixes as $value) {
                    $dirPath = $this->prefixer->stripPrefix($value['Prefix']);

                    yield new DirectoryAttributes($dirPath);

                    if ($deep) {
                        yield from $this->listContents($dirPath, $deep);
                    }
                }
            }

            $contents = $result['Contents'] ?? [];
            if ($contents) {
                foreach ($contents as $value) {
                    $key = $value['Key'] ?? '';
                    if (!$key) {
                        continue;
                    }

                    $size = (int) $value['Size'];
                    if (0 === $size && ($key === $prefixedPath.'/')) {
                        continue;
                    }

                    $filePath = $this->prefixer->stripPrefix($key);

                    yield new FileAttributes(
                        $filePath,
                        (int) $size,
                        null,
                        strtotime($value['LastModified'])
                    );
                }
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

        $this->client->copy($this->bucket, $prefixedDestination, [
            'Region' => $this->region,
            'Bucket' => $this->bucket,
            'Key'    => $prefixedSource,
        ]);
    }

    public function getUrl(string $path): string
    {
        $prefixedPath = $this->prefixer->prefixPath($path);

        if ($this->cdnDomain) {
            if (!preg_match('#https?://.+#i', $this->cdnDomain)) {
                return $this->schema.'://'.$this->cdnDomain.'/'.ltrim($prefixedPath, '/');
            }

            return $this->cdnDomain.'/'.ltrim($prefixedPath, '/');
        }

        return $this->schema.'://'.$this->bucket.'.cos.'.$this->region.'.myqcloud.com/'.ltrim($prefixedPath, '/');
    }

    public function getMetadata(string $path): ?FileAttributes
    {
        $prefixedPath = $this->prefixer->prefixPath($path);

        $result = $this->client->headObject([
            'Bucket' => $this->bucket,
            'Key'    => $prefixedPath,
        ]);

        if (empty($result)) {
            return null;
        }

        return new FileAttributes(
            $path,
            (int) $result['ContentLength'] ?? 0,
            null,
            strtotime($result['LastModified'] ?? ''),
            $result['ContentType'] ?? null,
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
