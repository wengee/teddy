<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Traits;

use Teddy\Interfaces\WebsocketHandlerInterface;

trait WebsocketAwareTrait
{
    /** @var bool */
    protected $websocketEnabled = false;

    /** @var null|WebsocketHandlerInterface */
    protected $websocketHandler;

    /** @param string|WebsocketHandlerInterface $handler */
    public function addWebsocketHandler($handler): void
    {
        if (is_subclass_of($handler, WebsocketHandlerInterface::class)) {
            $handler = is_string($handler) ? new $handler() : $handler;

            $this->websocketEnabled = true;
            $this->websocketHandler = $handler;
        }
    }

    public function getWebsocketHandler(): ?WebsocketHandlerInterface
    {
        if ($this->websocketEnabled) {
            return $this->websocketHandler;
        }

        return null;
    }
}
