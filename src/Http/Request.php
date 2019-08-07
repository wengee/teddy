<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-07 09:55:26 +0800
 */

namespace Teddy\Http;

use Slim\Psr7\Request as SlimRequest;

class Request extends SlimRequest
{
    protected $clientIp;

    public function getUserAgent()
    {
        return $this->getHeaderLine('User-Agent') ?: null;
    }

    public function getClientIp()
    {
        if (isset($this->clientIp)) {
            return $this->clientIp;
        }

        $ip = $this->getServerParam('REMOTE_ADDR');

        $httpClientIp = $this->getServerParam('HTTP_CLIENT_IP');
        if ($httpClientIp && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $httpClientIp) && !preg_match('#^(127|10|172\.16|192\.168)\.#', $httpClientIp)) {
            $ip = $httpClientIp;
        }

        $forwards = $this->getServerParam('HTTP_X_FORWARDED_FOR');
        if ($forwards && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $forwards, $matches)) {
            foreach ($matches[0] as $xip) {
                if (!preg_match('#^(127|10|172\.16|192\.168)\.#', $xip)) {
                    $ip = $xip;
                    break;
                }
            }
        }

        $ip = $ip == '::1' ? '127.0.0.1' : $ip;
        $this->clientIp = $ip;
        return $ip;
    }
}
