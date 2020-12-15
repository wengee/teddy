<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-12-15 17:18:02 +0800
 */

namespace Teddy\Interfaces;

interface ValidatorRuleInterface
{
    public function __invoke($value, array $data, callable $next);

    public function setLabel(?string $label);

    public function setField(?string $field);

    public function validateValue($value, array $data = []);
}
