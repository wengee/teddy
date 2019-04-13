<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-04-13 15:54:03 +0800
 */
namespace Teddy;

use Dotenv\Dotenv;
use Exception;

class Env
{
    protected $paths = [];

    public function __construct(array $paths = [])
    {
        array_push($paths, getcwd(), getenv('HOME') ?: '/');
        $this->paths = $paths;
    }

    public function load(string $file = '.env')
    {
        try {
            Dotenv::create($this->paths, $file)->load();
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    public static function loadFile(string $file = '.env', array $paths = [])
    {
        return (new self($paths))->load($file);
    }
}
