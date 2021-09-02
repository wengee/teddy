<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-08-30 11:48:04 +0800
 */

use Teddy\Config\Repository;

return [
    'default' => new Repository('start', Repository::DATA_PROTECTED),
    'list'    => new Repository([], Repository::DATA_AS_LIST | Repository::DATA_PROTECTED),
];
