<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-05-08 15:50:17 +0800
 */

namespace App\Validations;

use Teddy\Validation\Field;
use Teddy\Validation\Validation;

class Demo extends Validation
{
    protected function initialize(): void
    {
        $this->add('str1', 'Str1')->filter('trim')->then('required')->then('mobile');
        $this->add('int1', 'Int1')->filter('int');
        $this->add('int2')->filter('int')->then('lte', 12987, '错啦');
        $this->add('arr1')->filter('json_decode')->then([
            'a1' => Field::make('A1')->filter('float')->then('gt', 10),
            'a2' => Field::make(),
            'a3' => Field::make()->filter('bool'),
        ]);

        $this->add('arr2.b1')->if(function ($data) {
            return count($data['lst1']) > 5;
        });
        $this->add('arr2.b2')->if('arr1.a1', '>', 100);

        $this->add('lst1')->filter('list')->then('list', 'intval');
        $this->add('lst2')->filter('list')->then('list', [
            's1' => Field::make(),
            'i1' => Field::make()->filter('int'),
            'a1' => Field::make()->filter('array')->then('array', [
                'c1' => Field::make(),
                'c2' => Field::make('C2')->if('c3'),
            ]),
        ]);
    }
}
