<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-03-22 17:01:38 +0800
 */

namespace Teddy\Http;

use ArrayAccess;
use Illuminate\Support\Arr;
use Illuminate\Support\Traits\Macroable;
use Slim\Psr7\Request as SlimRequest;
use Teddy\Validation\Field;
use Teddy\Validation\Validation;

class Request extends SlimRequest implements ArrayAccess
{
    use Macroable;

    private int|float $timestamp = 0;

    private ?string $clientIp = null;

    public function offsetExists(mixed $offset): bool
    {
        return is_string($offset) && isset($this->attributes[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        if (is_string($offset)) {
            return $this->getAttribute($offset);
        }

        return null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_string($offset)) {
            $this->attributes[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        if (is_string($offset)) {
            unset($this->attributes[$offset]);
        }
    }

    public function getAttribute($name, $default = null)
    {
        return Arr::get($this->attributes, $name, $default);
    }

    public function getUserAgent()
    {
        return $this->getHeaderLine('User-Agent') ?: null;
    }

    public function getClientIp()
    {
        if (isset($this->clientIp)) {
            return $this->clientIp;
        }

        $ip = $this->getServerParam('HTTP_REMOTEIP') ?: $this->getServerParam('REMOTE_ADDR');

        $httpClientIp = $this->getServerParam('HTTP_CLIENT_IP');
        if ($httpClientIp && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $httpClientIp) && !preg_match('#^(127|10|172\.16|192\.168)\.#', $httpClientIp)) {
            $ip = $httpClientIp;
        }

        $forwards = $this->getServerParam('HTTP_X_ORIGINAL_FORWARDED_FOR') ?: $this->getServerParam('HTTP_X_FORWARDED_FOR');
        if ($forwards && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $forwards, $matches)) {
            foreach ($matches[0] as $xip) {
                if (!preg_match('#^(127|10|172\.16|192\.168)\.#', $xip)) {
                    $ip = $xip;

                    break;
                }
            }
        }

        $ip = '::1' == $ip ? '127.0.0.1' : $ip;

        $this->clientIp = $ip;

        return $ip;
    }

    public function getParam($key, $default = null)
    {
        $postParams = $this->getParsedBody();
        $getParams  = $this->getQueryParams();
        $result     = $default;
        if (is_array($postParams) && isset($postParams[$key])) {
            $result = $postParams[$key];
        } elseif (is_object($postParams) && property_exists($postParams, $key)) {
            $result = $postParams->{$key};
        } elseif (isset($getParams[$key])) {
            $result = $getParams[$key];
        }

        return $result;
    }

    public function getParams(array $only = null)
    {
        $params     = $this->getQueryParams();
        $postParams = $this->getParsedBody();
        if ($postParams) {
            $params = array_replace($params, (array) $postParams);
        }

        if ($only) {
            $onlyParams = [];
            foreach ($only as $key) {
                if (array_key_exists($key, $params)) {
                    $onlyParams[$key] = $params[$key];
                }
            }

            return $onlyParams;
        }

        return $params;
    }

    public function getServerParam($key, $default = null)
    {
        $serverParams = $this->getServerParams();

        return $serverParams[$key] ?? $default;
    }

    public function getCookieParam($key, $default = null)
    {
        $cookieParams = $this->getCookieParams();
        $result       = $default;
        if (isset($cookieParams[$key])) {
            $result = $cookieParams[$key];
        }

        return $result;
    }

    public function getQueryParam($key, $default = null)
    {
        $getParams = $this->getQueryParams();
        $result    = $default;
        if (isset($getParams[$key])) {
            $result = $getParams[$key];
        }

        return $result;
    }

    public function getParsedBodyParam($key, $default = null)
    {
        $postParams = $this->getParsedBody();
        $result     = $default;
        if (is_array($postParams) && isset($postParams[$key])) {
            $result = $postParams[$key];
        } elseif (is_object($postParams) && property_exists($postParams, $key)) {
            $result = $postParams->{$key};
        }

        return $result;
    }

    public function getUploadedFile(string $field)
    {
        return $this->uploadedFiles[$field] ?? null;
    }

    public function url(?string $path = null, array $query = []): string
    {
        if (preg_match('#^https?://.+$#', $path)) {
            return $path;
        }

        $uri = $this->getUri();
        if (empty($path) && empty($query)) {
            return (string) $uri;
        }
        if ($path) {
            $uri = $uri->withPath($path);
        }

        $query = $query ? http_build_query($query) : '';
        $uri   = $uri->withQuery($query);

        return (string) $uri;
    }

    public function timestamp(bool $asFloat = false)
    {
        if (!$this->timestamp) {
            $timestamp = $asFloat ?
                floatval($this->getServerParam('REQUEST_TIME_FLOAT')) :
                intval($this->getServerParam('REQUEST_TIME'));

            $this->timestamp = $timestamp > 0 ? $timestamp : time();
        }

        return $this->timestamp;
    }

    /**
     * @param string|Validation $validation
     * @param Field[]           $fields
     */
    public function validate($validation, array $fields = [])
    {
        return $this->validateParsedBody($validation, $fields);
    }

    /**
     * @param string|Validation $validation
     * @param Field[]           $fields
     */
    public function validateQuery($validation, array $fields = [])
    {
        $postData = (array) $this->getQueryParams();

        return validate($validation, $postData, $fields);
    }

    /**
     * @param string|Validation $validation
     * @param Field[]           $fields
     */
    public function checkQuery($validation, array $fields = [])
    {
        $postData = (array) $this->getQueryParams();

        return validate($validation, $postData, $fields, true);
    }

    /**
     * @param string|Validation $validation
     * @param Field[]           $fields
     */
    public function validateParsedBody($validation, array $fields = [])
    {
        $postData = (array) $this->getParsedBody();

        return validate($validation, $postData, $fields);
    }

    /**
     * @param string|Validation $validation
     * @param Field[]           $fields
     */
    public function checkParsedBody($validation, array $fields = [])
    {
        $postData = (array) $this->getParsedBody();

        return validate($validation, $postData, $fields, true);
    }

    public function getPageInfo(int $pageSize = 0): array
    {
        $currentPage = max(1, (int) $this->getParam('page', 1));
        if (0 === $pageSize) {
            $pageSize = (int) $this->getParam('pageSize', 0);
            if ($pageSize <= 0 || $pageSize > 100 || !in_array($pageSize, [10, 20, 50, 100])) {
                $pageSize = 20;
            }
        }

        return [$currentPage, $pageSize];
    }
}
