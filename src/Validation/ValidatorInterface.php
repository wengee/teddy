<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-05 18:33:47 +0800
 */
namespace Teddy\Validation;

interface ValidatorInterface
{
    public function __invoke($value, array $data, callable $next);

    public function setName(string $name);

    public function setLabel(string $label);
}
