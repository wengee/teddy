<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-07-20 14:12:05 +0800
 */

namespace App\Controllers;

use App\Models\Qrcode;
use Teddy\Controller;
use Teddy\Http\Request;
use Teddy\Http\Response;

class IndexController extends Controller
{
    public function index(Request $request, Response $response)
    {
        $query = Qrcode::query()
            ->select()
            ->where([
                ['id', 0],
                ['id', '>', 100],
                ['id', '<>', [1, 20]],
            ], 'OR')
            ->where([
                ['code', 'abc'],
                ['code', '~', 'c%'],
            ], 'OR')
            ->where([
                ['status', '!=', null],
                ['status', '>', 100],
                ['status', '!=', [1,2,3]],
                [['status', 'code'], '%', '*a*']
            ], 'OR');

        $b = [];
        $a = $query->getSql($b);

        // $c = new Qrcode;
        $c = app('auth')->create([]);

        return $response->json(0, compact(['a', 'b', 'c']));
    }

    public function upload(Request $request, Response $response)
    {
        $files = $request->getUploadedFiles();
        $file = $files['file'] ?? null;
        if (!$file) {
            return $response->json('请上传文件');
        }

        $filename = (string) $file->getClientFilename();
        $ext = $this->guessFileExt($filename);
        $path = 'test/' . time() . str_random(16) . '.' . $ext;

        $f = $file->getStream()->detach();
        defer(function () use ($f): void {
            fclose($f);
        });

        app('fs')->writeStream($path, $f);
        $url = app('fs')->url($path);

        return $response->json(0, [
            'url' => $url,
        ]);
    }

    protected function guessFileExt(string $name)
    {
        $ext = null;
        if (strrpos($name, '.') !== false) {
            $ext = substr($name, strrpos($name, '.') + 1);
            $ext = strtolower($ext);
        }

        return $ext;
    }
}
