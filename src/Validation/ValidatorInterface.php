<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-05 10:02:48 +0800
 */
namespace Teddy\Validation;

interface ValidatorInterface
{
    public function setName(string $name);

    public function validate($value, array $data);
}
