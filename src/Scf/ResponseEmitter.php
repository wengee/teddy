<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-12-19 12:00:47 +0800
 */

namespace Teddy\Scf;

use Psr\Http\Message\ResponseInterface;

class ResponseEmitter
{
    public static function emit(ResponseInterface $response): array
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

            $body = $response->getBody()->getContents();
            $ret['isBase64Encoded'] = true;
            $ret['body'] = base64_encode($body);
        }

        return $ret;
    }
}
