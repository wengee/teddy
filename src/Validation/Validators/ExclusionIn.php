<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-22 17:57:52 +0800
 */
namespace Teddy\Validation\Validators;

class ExclusionIn extends ValidatorBase
{
    public function validate($value, array $options = [])
    {
        $domain = array_get($options, 'domain');
        if (!is_array($domain)) {
            $this->error('Option "domain" must be an array.');
        }

        $strict = (bool) array_get($options, 'strict', false);
        if (in_array($value, $domain, $strict)) {
            $this->error(
                'Field :label must not be a part of list: :domain',
                $options
            );
        }
    }
}
