<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-08-31 11:53:38 +0800
 */

use Teddy\Config\Repository;

return [
    'enabled'   => false,
    'secret'    => 'This is a secret!',
    'algorithm' => new Repository(['HS256', 'HS512', 'HS384'], Repository::DATA_AS_LIST | Repository::DATA_PROTECTED),
];
