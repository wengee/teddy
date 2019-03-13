<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-08 14:23:54 +0800
 */
/**
 * 基于雨润Guzzle-Swoole修改，来自：https://gitee.com/yurunsoft/Guzzle-Swoole
 */
namespace SlimExtra\Swoole;

use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\RequestInterface;
use Swoole\Coroutine\Http\Client;

class GuzzleHandler
{
    public function __invoke(RequestInterface $request, array $options)
    {
        $uri = $request->getUri();
        $isLocation = false;
        $count = 0;
        $response = null;
        do {
            $port = $uri->getPort();
            if (empty($port)) {
                if ('https' === $uri->getScheme()) {
                    $port = 443;
                } else {
                    $port = 80;
                }
            } else {
                $port = (int) $port;
            }

            $client = new Client(
                $uri->getHost(),
                $port,
                'https' === $uri->getScheme()
            );

            if ($isLocation) {
                $client->setMethod('GET');
            } else {
                $client->setMethod($request->getMethod());
            }

            if (!$isLocation) {
                $client->setData((string) $request->getBody());
            }

            $settings = [];
            $settings = $this->parseSSL($request, $options, $settings);
            $settings = $this->parseProxy($request, $options, $settings);
            $settings = $this->parseNetwork($request, $options, $settings);

            $headers = [];
            foreach ($request->getHeaders() as $name => $value) {
                if ($name !== 'Content-Length') {
                    $headers[$name] = implode('; ', $value);
                }
            }
            $client->setHeaders($headers);

            if (!empty($settings)) {
                $client->set($settings);
            }

            $path = $uri->getPath();
            if (empty($path)) {
                $path = '/';
            }

            $query = $uri->getQuery();
            if (!empty($query)) {
                $path .= '?' . $query;
            }

            $client->execute($path);
            $response = $this->getResponse($client);

            $statusCode = $response->getStatusCode();
            if ((301 === $statusCode || 302 === $statusCode) && $options[RequestOptions::ALLOW_REDIRECTS] && ++$count <= $options[RequestOptions::ALLOW_REDIRECTS]['max']) {
                $uri = new Uri($response->getHeaderLine('location'));
                $isLocation = true;
            } else {
                break;
            }
        } while (true);

        return new FulfilledPromise($response);
    }

    private function parseSSL(RequestInterface $request, array $options, array $settings = []): array
    {
        if (($verify = $options['verify'])) {
            $settings['ssl_verify_peer'] = true;
            if (is_string($verify)) {
                $settings['ssl_cafile'] = $verify;
            }
        } else {
            $settings['ssl_verify_peer'] = false;
        }
        $cert = isset($options['cert']) ? $options['cert'] : [];
        if (isset($options['cert'])) {
            $cert = (array) $options['cert'];
            $settings['ssl_cert_file'] = $cert[0];
        }

        if (isset($options['ssl_key'])) {
            $cert = (array) $options['ssl_key'];
            $settings['ssl_key_file'] = $key[0];
        }

        return $settings;
    }

    private function parseProxy(RequestInterface $request, array $options, array $settings = []): array
    {
        $proxy = isset($options['proxy']) ? $options['proxy'] : [];
        if (isset($proxy['no']) && \GuzzleHttp\is_host_in_noproxy($request->getUri()->getHost(), $proxy['no'])) {
            return $settings;
        }

        $scheme = $request->getUri()->getScheme();
        $proxyUri = isset($proxy[$scheme]) ? $proxy[$scheme] : null;
        if (null === $proxyUri) {
            return $settings;
        }
        $proxyUri = new Uri($proxyUri);
        $userinfo = explode(':', $proxyUri->getUserInfo());
        if (isset($userinfo[1])) {
            list($username, $password) = $userinfo;
        } else {
            $username = $userinfo[0];
            $password = null;
        }
        $settings['http_proxy_host'] = $proxyUri->getHost();
        $settings['http_proxy_port'] = $proxyUri->getPort();
        $settings['http_proxy_user'] = $username;
        $settings['http_proxy_password'] = $password;
        return $settings;
    }

    private function parseNetwork(RequestInterface &$request, array $options, array $settings = []): array
    {
        // 用户名密码认证处理
        $auth = isset($options['auth']) ? $options['auth'] : [];
        if (isset($auth[1])) {
            list($username, $password) = $auth;
            $auth = base64_encode($username . ':' . $password);
            $request = $request->withAddedHeader('Authorization', 'Basic ' . $auth);
        }
        // 超时
        if (isset($options['timeout']) && $options['timeout'] > 0) {
            $settings['timeout'] = $options['timeout'];
        } else {
            $settings['timeout'] = 10;
        }

        return $settings;
    }

    private function getResponse(Client $client): Response
    {
        $headers = isset($client->headers) ? $client->headers : [];
        if (isset($headers['set-cookie'])) {
            $headers['set-cookie'] = $client->set_cookie_headers;
        }
        $response = new Response($client->statusCode, $headers, $client->body);
        return $response;
    }
}
