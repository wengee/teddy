<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

use Teddy\Config\Repository;

return [
    'intercept'       => new Repository(true, Repository::DATA_PROTECTED),
    'origin'          => '*',
    'methods'         => new Repository([
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
    ], Repository::DATA_AS_LIST | Repository::DATA_PROTECTED),
    'withCredentials' => new Repository(false, Repository::DATA_PROTECTED),
    'headers'         => new Repository([
        'Accept',
        'Accept-Language',
        'User-Agent',
        'X-Requested-With',
        'If-Modified-Since',
        'Cache-Control',
        'Content-Type',
        'Range',
        'Authorization',
    ], Repository::DATA_AS_LIST | Repository::DATA_PROTECTED),
];
