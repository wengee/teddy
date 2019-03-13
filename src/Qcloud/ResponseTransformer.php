<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-12 18:39:36 +0800
 */
namespace Teddy\Qcloud;

use Slim\Http\Response;

class ResponseTransformer
{
    public static function mergeToArray(Response $response): array
    {
        $ret = [
            'isBase64Encoded' => false,
            'statusCode' => 200,
            'headers' => [],
            'body' => '',
        ];

        $size = $response->getBody()->getSize();
        if ($size !== null) {
            $ret['headers']['Content-Length'] = (string) $size;
        }

        if (!empty($response->getHeaders())) {
            foreach ($response->getHeaders() as $key => $headerArray) {
                if (stripos($key, 'Set-Cookie') === 0) {
                    $n = 0;
                    foreach ($headerArray as $value) {
                        $ret['headers'][$key . str_repeat(' ', $n++)] = $value;
                    }
                } else {
                    $ret['headers'][$key] = implode('; ', $headerArray);
                }
            }
        }

        $ret['statusCode'] = (int) $response->getStatusCode();
        if ($size > 0) {
            if ($response->getBody()->isSeekable()) {
                $response->getBody()->rewind();
            }

            $ret['body'] = $response->getBody()->getContents();
        }

        return $ret;
    }
}
