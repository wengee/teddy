<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-07-22 11:17:43 +0800
 */

return [
    // time slots: second minute hour day month week
    // e.g. ['*/5 * * * *', App\Tasks\Demo::class]
    ['*/5 * * * * *', App\Tasks\Demo::class],
];
