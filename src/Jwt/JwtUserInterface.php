<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-07 16:57:57 +0800
 */
namespace SlimExtra\Jwt;

interface JwtUserInterface
{
    public static function retrieveByPayload($payload);
}
