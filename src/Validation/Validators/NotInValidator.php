<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-03-22 16:33:21 +0800
 */

namespace Teddy\Validation\Validators;

use Teddy\Validation\Field;

class NotInValidator extends Validator
{
    protected array $domain = [];

    protected string $message = ':label不在有效范围内';

    public function __construct(Field $field, array $domain, ?string $message = null)
    {
        $this->domain = $domain;
        parent::__construct($field, $message);
    }

    public function validate($value, array $data, callable $next)
    {
        if (in_array($value, $this->domain, true)) {
            $this->throwError();
        }

        return $next($value, $data);
    }
}
