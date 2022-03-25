<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-03-08 10:33:21 +0800
 */

use Teddy\Config\Repository;

return new Repository([], Repository::DATA_AS_LIST | Repository::DATA_PROTECTED);
