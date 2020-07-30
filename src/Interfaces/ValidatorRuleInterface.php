<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-07-30 15:27:18 +0800
 */

namespace Teddy\Interfaces;

interface ValidatorRuleInterface
{
    public function __invoke($value, array $data, callable $next);

    public function setLabel(?string $label);

    public function validateValue($value, array $data = []);
}
