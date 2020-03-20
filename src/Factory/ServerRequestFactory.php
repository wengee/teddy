<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-03-20 17:10:27 +0800
 */

namespace Teddy\Factory;

use Slim\Psr7\Cookies;
use Slim\Psr7\Headers;
use Slim\Psr7\UploadedFile;
use Slim\Psr7\Factory\UriFactory;
use Slim\Psr7\Factory\StreamFactory;
use Psr\Http\Message\ServerRequestInterface;

class ServerRequestFactory
{
    public static function createServerRequest(): ServerRequestInterface
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = (new UriFactory())->createFromGlobals($_SERVER);

        $headers = Headers::createFromGlobals();
        $cookies = Cookies::parseHeader($headers->getHeader('Cookie', []));

        $body = (new StreamFactory())->createStreamFromFile('php://input');
        $uploadedFiles = UploadedFile::createFromGlobals($_SERVER);

        $request = make('request', [$method, $uri, $headers, $cookies, $_SERVER, $body, $uploadedFiles]);
        $contentTypes = $request->getHeader('Content-Type') ?? [];

        $parsedContentType = '';
        foreach ($contentTypes as $contentType) {
            $fragments = explode(';', $contentType);
            $parsedContentType = current($fragments);
        }

        $contentTypesWithParsedBodies = ['application/x-www-form-urlencoded', 'multipart/form-data'];
        if ($method === 'POST' && in_array($parsedContentType, $contentTypesWithParsedBodies)) {
            return $request->withParsedBody($_POST);
        }

        return $request;
    }
}
