<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 14:22:07 +0800
 */

namespace Teddy\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Stream;
use Teddy\Http\Response;

class StaticFileMiddleware implements MiddlewareInterface
{
    protected static $mimeTypes = [
        'txt'   => 'text/plain',
        'htm'   => 'text/html',
        'html'  => 'text/html',
        'php'   => 'text/html',
        'css'   => 'text/css',
        'js'    => 'application/javascript',
        'json'  => 'application/json',
        'xml'   => 'application/xml',
        'swf'   => 'application/x-shockwave-flash',
        'flv'   => 'video/x-flv',

        // images
        'png'   => 'image/png',
        'jpe'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'jpg'   => 'image/jpeg',
        'gif'   => 'image/gif',
        'bmp'   => 'image/bmp',
        'ico'   => 'image/vnd.microsoft.icon',
        'tiff'  => 'image/tiff',
        'tif'   => 'image/tiff',
        'svg'   => 'image/svg+xml',
        'svgz'  => 'image/svg+xml',

        // archives
        'zip'   => 'application/zip',
        'rar'   => 'application/x-rar-compressed',
        'exe'   => 'application/x-msdownload',
        'msi'   => 'application/x-msdownload',
        'cab'   => 'application/vnd.ms-cab-compressed',

        // audio/video
        'mp3'   => 'audio/mpeg',
        'qt'    => 'video/quicktime',
        'mov'   => 'video/quicktime',

        // adobe
        'pdf'   => 'application/pdf',
        'psd'   => 'image/vnd.adobe.photoshop',
        'ai'    => 'application/postscript',
        'eps'   => 'application/postscript',
        'ps'    => 'application/postscript',

        // ms office
        'doc'   => 'application/msword',
        'rtf'   => 'application/rtf',
        'xls'   => 'application/vnd.ms-excel',
        'ppt'   => 'application/vnd.ms-powerpoint',

        // open office
        'odt'   => 'application/vnd.oasis.opendocument.text',
        'ods'   => 'application/vnd.oasis.opendocument.spreadsheet',
    ];

    protected $basePath;

    protected $urlPrefix;

    public function __construct(string $basePath, string $urlPrefix = '')
    {
        $this->basePath  = rtrim($basePath, '/').'/';
        $this->urlPrefix = $urlPrefix ? '/'.ltrim($urlPrefix, '/') : '';
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        if (!$this->urlPrefix || 0 === strpos($path, $this->urlPrefix)) {
            $filePath = $this->basePath.ltrim($path, '/');
            if (is_file($filePath)) {
                return $this->sendFile($filePath);
            }
            if (is_dir($filePath)) {
                $filePath = rtrim($filePath, '/').'/index.html';
                if (is_file($filePath)) {
                    return $this->sendFile($filePath);
                }
            }
        }

        return $handler->handle($request);
    }

    protected function sendFile(string $filePath): ResponseInterface
    {
        $response = response();
        if ($response instanceof Response) {
            return $response->withSendFile($filePath);
        }

        return $response->withHeader('Content-Type', $this->getMimeType($filePath))
            ->withBody(new Stream(fopen($filePath, 'r')))
        ;
    }

    protected function getMimeType(string $filePath): string
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if (isset(self::$mimeTypes[$ext])) {
            return self::$mimeTypes[$ext];
        }
        if (function_exists('finfo_open')) {
            $finfo    = finfo_open(FILEINFO_MIME);
            $mimeType = finfo_file($finfo, $filePath);
            finfo_close($finfo);

            return $mimeType;
        }

        return 'application/octet-stream';
    }
}
