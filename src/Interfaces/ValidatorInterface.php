<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-07-01 15:58:35 +0800
 */

namespace Teddy\Interfaces;

interface ValidatorInterface
{
    public function validate($value, array $data, callable $next);
}
