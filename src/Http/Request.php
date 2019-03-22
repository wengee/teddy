<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-22 18:23:50 +0800
 */
namespace Teddy\Http;

use Carbon\Carbon;
use Slim\Http\Request as SlimRequest;

class Request extends SlimRequest
{
    private $timestamp;

    private $now;

    public function __get($key)
    {
        return $this->attributes->get($key);
    }

    public function getUploadedFile(string $field)
    {
        return isset($this->uploadedFiles[$field]) ? $this->uploadedFiles[$field] : null;
    }

    public function url(?string $path = null, array $query = []): string
    {
        if (preg_match('#^https?://.+$#', $path)) {
            return $path;
        }

        $uri = $this->getUri();
        if (empty($path) && empty($query)) {
            return (string) $uri;
        } else {
            if ($path) {
                $uri = $uri->withPath($path);
            }

            $query = $query ? http_build_query($query) : '';
            $uri = $uri->withQuery($query);
            return (string) $uri;
        }
    }

    public function timestamp(bool $asFloat = false)
    {
        if (!$this->timestamp) {
            $timestamp = $asFloat ?
                $this->getServerParam('REQUEST_TIME_FLOAT') :
                $this->getServerParam('REQUEST_TIME');

            $this->timestamp = $timestamp ?: time();
        }

        return $this->timestamp;
    }

    public function now(): Carbon
    {
        if (!$this->now) {
            $this->now = Carbon::createFromTimestamp($this->timestamp());
        }

        return $this->now;
    }

    public function filter(array $fields)
    {
        $ret = [];
        $params = $this->getParams();
        $filter = app('filter');
        foreach ($fields as $key => $value) {
            if (is_int($key)) {
                $ret[$value] = array_get($params, $value);
            } else {
                $paramValue = array_get($params, $key);
                $ret[$key] = $filter->sanitize($paramValue, $value);
            }
        }

        return $ret;
    }

    public function validate(array $rules)
    {
        $input = (array) $this->getParsedBody();
        return app('validation')->validate($input, $rules);
    }
}
