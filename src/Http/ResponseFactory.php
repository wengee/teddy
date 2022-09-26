<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-09-26 15:59:16 +0800
 */

namespace Teddy\Http;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Teddy\Interfaces\ContainerAwareInterface;
use Teddy\Interfaces\ContainerInterface;
use Teddy\Traits\ContainerAwareTrait;

class ResponseFactory implements ContainerAwareInterface, ResponseFactoryInterface
{
    use ContainerAwareTrait;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function createResponse(
        int $code = StatusCodeInterface::STATUS_OK,
        string $reasonPhrase = ''
    ): ResponseInterface {
        /**
         * @var ResponseInterface
         */
        $response = $this->getContainer()->getNew(ResponseInterface::class, [$code]);

        return $response->withStatus($code, $reasonPhrase);
    }
}
