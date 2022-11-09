<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-09 22:42:34 +0800
 */

return [
    'key'          => 'queue:',
    'redis'        => 'default',
    'retrySeconds' => 5,
    'maxAttempts'  => 5,
    'channels'     => null,
];
