<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-03-22 15:54:34 +0800
 */

namespace Teddy\Http;

use Fig\Http\Message\StatusCodeInterface;
use Illuminate\Support\Traits\Macroable;
use JsonSerializable;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Slim\Psr7\Response as SlimResponse;
use Slim\Psr7\Stream;
use Teddy\Interfaces\CookieAwareInterface;
use Teddy\Interfaces\FileResponseInterface;
use Throwable;

class Response extends SlimResponse implements CookieAwareInterface, FileResponseInterface
{
    use Macroable;

    protected array $cookies = [];

    protected string $sendFile = '';

    protected bool $isJsonResponse = false;

    protected int $jsonEncodingOptions = 0;

    /**
     * @var mixed
     */
    protected $jsonData;

    public function isJsonResponse(&$data): bool
    {
        if ($this->isJsonResponse) {
            $data = $this->jsonData;
        }

        return (bool) $this->isJsonResponse;
    }

    public function withSendFile(string $file): ResponseInterface
    {
        $clone = clone $this;

        $clone->sendFile = $file;

        return $clone;
    }

    public function getSendFile(): string
    {
        return $this->sendFile;
    }

    public function write($data): ResponseInterface
    {
        $this->getBody()->write($data);

        return $this;
    }

    public function withJson($data, int $status = StatusCodeInterface::STATUS_OK): ResponseInterface
    {
        $this->isJsonResponse = true;
        $this->jsonData       = $data;

        $response = $this->withBody(new Stream(fopen('php://temp', 'r+')));

        $response->body->write($json = json_encode($data, $this->jsonEncodingOptions));
        if (false === $json) {
            throw new RuntimeException(json_last_error_msg(), json_last_error());
        }

        $responseWithJson = $response->withHeader('Content-Type', 'application/json');
        if (isset($status)) {
            return $responseWithJson->withStatus($status);
        }

        return $responseWithJson;
    }

    public function redirect($url, $status = StatusCodeInterface::STATUS_FOUND): ResponseInterface
    {
        $responseWithRedirect = $this->withHeader('Location', (string) $url);

        if (null === $status && StatusCodeInterface::STATUS_OK === $this->getStatusCode()) {
            $status = StatusCodeInterface::STATUS_FOUND;
        }

        if (null !== $status) {
            return $responseWithRedirect->withStatus($status);
        }

        return $responseWithRedirect;
    }

    public function json(...$args): ResponseInterface
    {
        $data = ['errmsg' => null, 'errcode' => -1];
        foreach ($args as $arg) {
            if ($arg instanceof JsonSerializable) {
                $data = $arg;

                break;
            }

            if ($arg instanceof Throwable) {
                $data['errcode'] = $arg->getCode() ?: -1;
                $data['errmsg']  = $arg->getMessage();
            } elseif (is_int($arg)) {
                $data['errcode'] = $arg;
            } elseif (is_string($arg)) {
                $data['errmsg'] = $arg;
            } else {
                $data = array_merge($data, (array) $arg);
            }
        }

        return $this->withJson($data, StatusCodeInterface::STATUS_OK);
    }

    public function setCookie(string $name, ?string $value = null, int $maxAge = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = true, bool $sameSite = false): ResponseInterface
    {
        if (null === $value) {
            $maxAge = 1;
        }

        $clone  = clone $this;
        $domain = $domain ?: config('cookie.domain', '');

        $clone->cookies[$name] = compact('value', 'maxAge', 'path', 'domain', 'secure', 'httpOnly', 'sameSite');

        return $clone;
    }

    public function getHeaders(): array
    {
        $headers = (array) parent::getHeaders();
        if ($this->cookies) {
            $headers['Set-Cookie'] = [];
            foreach ($this->cookies as $name => $properties) {
                $headers['Set-Cookie'][] = $this->parseCookie($name, $properties);
            }
        }

        return $headers;
    }

    public function getCookies(): array
    {
        return $this->cookies;
    }

    protected function parseCookie($name, array $properties): string
    {
        $result = urlencode($name).'='.urlencode($properties['value']);

        if (isset($properties['domain'])) {
            $result .= '; domain='.$properties['domain'];
        }

        if (isset($properties['path'])) {
            $result .= '; path='.$properties['path'];
        }

        if (isset($properties['expire'])) {
            $timestamp = (int) $properties['expire'];

            if (0 !== $timestamp) {
                $result .= '; expires='.gmdate('D, d-M-Y H:i:s e', $timestamp);
            }
        }

        if (isset($properties['secure']) && $properties['secure']) {
            $result .= '; secure';
        }

        if (isset($properties['hostonly']) && $properties['hostonly']) {
            $result .= '; HostOnly';
        }

        if (isset($properties['httponly']) && $properties['httponly']) {
            $result .= '; HttpOnly';
        }

        if (isset($properties['samesite']) && in_array(strtolower($properties['samesite']), ['lax', 'strict'], true)) {
            // While strtolower is needed for correct comparison, the RFC doesn't care about case
            $result .= '; SameSite='.$properties['samesite'];
        }

        return $result;
    }
}
