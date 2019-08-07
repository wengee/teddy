<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-07 18:00:36 +0800
 */

namespace Teddy\Validation\Validators;

use Teddy\Filter;
use Teddy\Validation\ValidatorRuleInterface;

class DataList extends ValidatorRuleBase
{
    protected $filter;

    protected $rule;

    protected $message = ':label列表数据验证不通过';

    public function __construct(?ValidatorRuleInterface $rule, $filter = null)
    {
        $this->rule = $rule;
        $this->filter = $filter;
    }

    protected function validate($value, array $data, callable $next)
    {
        $ret = [];
        $value = array_values((array) $value);
        foreach ($value as $v) {
            $v = $this->filterValue($v);
            $ret[] = $this->validateItem($v, $data);
        }

        return $next($ret, $data);
    }

    protected function validateItem($item, array $data)
    {
        if (!$this->rule) {
            return $item;
        }

        return $this->rule->validateValue($item, $data);
    }

    protected function filterValue($value)
    {
        if (empty($this->filter)) {
            return $value;
        }

        return Filter::instance()->sanitize($value, $this->filter);
    }
}
