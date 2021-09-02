<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-08-30 17:36:22 +0800
 */

return [
    'schedule' => [
        'enabled' => true,
        'list'    => [
            ['* * * * *', App\Tasks\Demo::class],
        ],
    ],
];
