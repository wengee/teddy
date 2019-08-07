<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-07 18:33:09 +0800
 */

namespace Teddy\Interfaces;

interface JwtUserInterface
{
    public static function retrieveByPayload($payload);
}
