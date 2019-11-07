<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-11-07 18:01:17 +0800
 */

namespace GuzzleHttp;

use Teddy\Guzzle\DefaultHandler;

/**
 * Chooses and creates a default handler to use based on the environment.
 *
 * The returned handler is not wrapped by any default middlewares.
 *
 * @throws \RuntimeException if no viable Handler is available.
 * @return callable Returns the best handler for the given system.
 */
function choose_handler()
{
    return DefaultHandler::get();
}
