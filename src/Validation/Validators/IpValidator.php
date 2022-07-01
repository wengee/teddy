<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Validation\Validators;

use Teddy\Validation\Field;

class IpValidator extends Validator
{
    protected $flag;

    protected $message = ':label不是合法的IP格式';

    public function __construct(Field $field, int $flag = FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6, ?string $message = null)
    {
        $this->flag = $flag;
        parent::__construct($field, $message);
    }

    public function validate($value, array $data, callable $next)
    {
        if (!filter_var((string) $value, FILTER_VALIDATE_IP, $this->flag)) {
            $this->throwError();
        }

        return $next($value, $data);
    }
}
