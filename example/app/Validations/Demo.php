<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-05-07 16:14:35 +0800
 */

namespace App\Validations;

use Teddy\Validation\Fields\Field;
use Teddy\Validation\Validation;

class Demo extends Validation
{
    protected function initialize(): void
    {
        $this->trim('str1', 'Str1')->then('required')->then('mobile');
        $this->int('int1', 'Int1');
        $this->int('int2')->then('lte', 12987, '错啦');
        $this->array('arr1')->json()->then([
            'a1' => Field::float('A1')->then('gt', 10),
            'a2' => Field::string(),
            'a3' => Field::bool(),
        ]);

        $this->string('arr2.b1')->if(function ($data) {
            return count($data['lst1']) > 5;
        });
        $this->string('arr2.b2')->if('arr1.a1', '>', 100);

        $this->list('lst1')->then('list', 'intval');
        $this->list('lst2')->then('list', [
            's1' => Field::string(),
            'i1' => Field::int(),
            'a1' => Field::array()->then('array', [
                'c1' => Field::string(),
                'c2' => Field::string('C2')->then('optional'),
            ]),
        ]);
        $this->list('lst3')->split();
    }
}
