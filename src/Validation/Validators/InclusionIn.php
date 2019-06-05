<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-05 14:29:07 +0800
 */
namespace Teddy\Validation\Validators;

class InclusionIn extends ValidatorBase
{
    protected $domain = [];

    protected $strict = false;

    protected $message = [
        'default' => ':label不在有效范围内',
    ];

    public function __construct(array $domain, bool $strict = false)
    {
        $this->domain = $domain;
        $this->strict = $strict;
    }

    public function validate($value, array $data)
    {
        if (!in_array($value, $this->domain, $this->strict)) {
            $this->throwMessage();
        }

        return $value;
    }
}
