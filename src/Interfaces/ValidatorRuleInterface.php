<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-07 17:58:34 +0800
 */

namespace Teddy\Interfaces;

interface ValidatorRuleInterface
{
    public function __invoke($value, array $data, callable $next);

    public function validateValue($value, array $data);
}
