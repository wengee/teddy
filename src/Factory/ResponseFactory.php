<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 13:16:22 +0800
 */

namespace Teddy\Factory;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Teddy\Container\Container;

class ResponseFactory implements ResponseFactoryInterface
{
    public function createResponse(
        int $code = StatusCodeInterface::STATUS_OK,
        string $reasonPhrase = ''
    ): ResponseInterface {
        /** @var ResponseInterface */
        $response = Container::getInstance()->getNew('response', [$code]);

        return $response->withStatus($code, $reasonPhrase);
    }
}
