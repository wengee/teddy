<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-22 16:29:32 +0800
 */
namespace Teddy\Validation;

interface ValidatorInterface
{
    public function validate($value, array $options = []);
}
