<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-04-09 14:20:38 +0800
 */
namespace Teddy;

use Exception;

class JsonResponse extends Exception implements JsonInterface
{
    protected $data = ['errmsg' => null, 'errcode' => -1];

    public function __construct(...$args)
    {
        foreach ($args as $arg) {
            if ($arg instanceof Exception) {
                $this->data['errcode'] = $arg->getCode() ?: -1;
                $this->data['errmsg'] = $arg->getMessage();
            } elseif (is_int($arg)) {
                $this->data['errcode'] = $arg;
            } elseif (is_string($arg)) {
                $this->data['errmsg'] = $arg;
            } else {
                $this->data = array_merge($this->data, (array) $arg);
            }
        }
    }

    public function encode(): string
    {
        return json_encode($this->data);
    }

    public function jsonSerialize()
    {
        return $this->data;
    }
}
