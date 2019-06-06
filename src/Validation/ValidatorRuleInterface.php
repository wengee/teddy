<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-06 16:40:07 +0800
 */
namespace Teddy\Validation;

interface ValidatorRuleInterface
{
    public function __invoke($value, array $data, callable $next);

    public function validateValue($value, array $data);
}
