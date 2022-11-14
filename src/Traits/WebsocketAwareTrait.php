<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-14 20:29:56 +0800
 */

namespace Teddy\Traits;

use Exception;
use Teddy\Interfaces\WebsocketHandlerInterface;
use Teddy\Websocket\CloseException;

trait WebsocketAwareTrait
{
    /**
     * @var null|WebsocketHandlerInterface
     */
    protected $handler;

    protected function handleEvent(string $method, ...$args): bool
    {
        if (!$this->handler) {
            return false;
        }

        try {
            call_user_func([$this->handler, $method], ...$args);
        } catch (CloseException $e) {
            return false;
        } catch (Exception $e) {
            log_exception($e);
        }

        return true;
    }
}
