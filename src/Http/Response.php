<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-08-05 14:38:46 +0800
 */

namespace Teddy\Http;

use Exception;
use Fig\Http\Message\StatusCodeInterface;
use Illuminate\Support\Traits\Macroable;
use JsonSerializable;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Slim\Psr7\Response as SlimResponse;
use Slim\Psr7\Stream;

class Response extends SlimResponse
{
    use Macroable;

    protected $cookies = [];

    protected $sendFile;

    protected $isJsonResponse = false;

    protected $jsonData = null;

    public function isJsonResponse(&$data): bool
    {
        if ($this->isJsonResponse) {
            $data = $this->jsonData;
        }

        return !!$this->isJsonResponse;
    }

    public function withSendFile(string $file): ResponseInterface
    {
        $clone = clone $this;
        $clone->sendFile = $file;
        return $clone;
    }

    public function getSendFile(): string
    {
        return (string) $this->sendFile;
    }

    public function write($data): ResponseInterface
    {
        $this->getBody()->write($data);
        return $this;
    }

    public function withJson($data, $status = StatusCodeInterface::STATUS_OK, $encodingOptions = 0): ResponseInterface
    {
        $this->isJsonResponse = true;
        $this->jsonData = $data;

        $response = $this->withBody(new Stream(fopen('php://temp', 'r+')));
        $response->body->write($json = json_encode($data, $encodingOptions));

        // Ensure that the json encoding passed successfully
        if ($json === false) {
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

        if ($status === null && $this->getStatusCode() === StatusCodeInterface::STATUS_OK) {
            $status = StatusCodeInterface::STATUS_FOUND;
        }

        if ($status !== null) {
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

            if ($arg instanceof Exception) {
                $data['errcode'] = $arg->getCode() ?: -1;
                $data['errmsg'] = $arg->getMessage();
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

    public function setCookie(string $name, ?string $value = null, int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = true): ResponseInterface
    {
        if ($value === null) {
            $expire = 1;
        }

        $clone = clone $this;
        $domain = $domain ?: config('cookie.domain', '');
        $clone->cookies[$name] = compact('value', 'expire', 'path', 'domain', 'secure', 'httponly');
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
        $result = urlencode($name) . '=' . urlencode($properties['value']);

        if (isset($properties['domain'])) {
            $result .= '; domain=' . $properties['domain'];
        }

        if (isset($properties['path'])) {
            $result .= '; path=' . $properties['path'];
        }

        if (isset($properties['expire'])) {
            $timestamp = (int) $properties['expire'];

            if ($timestamp !== 0) {
                $result .= '; expires=' . gmdate('D, d-M-Y H:i:s e', $timestamp);
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
            $result .= '; SameSite=' . $properties['samesite'];
        }

        return $result;
    }
}
