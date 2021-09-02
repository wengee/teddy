<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-02 14:34:40 +0800
 */

namespace App\Controllers;

use App\Models\Abc;
use Illuminate\Support\Str;
use Teddy\Controller;
use Teddy\Http\Request;
use Teddy\Http\Response;

class IndexController extends Controller
{
    public function index(Request $request, Response $response)
    {
        $model = new Abc();
        $model = $model->save() ? $model : null;

        return $response->json(0, [
            'config'    => config(),
            'container' => $this->getContainer(),
            'model'     => $model,
            'list'      => Abc::query()->orderBy('id', 'DESC')->limit(5)->all(),
        ]);
    }

    public function upload(Request $request, Response $response)
    {
        $files = $request->getUploadedFiles();
        $file  = $files['file'] ?? null;
        if (!$file) {
            return $response->json('请上传文件');
        }

        $filename = (string) $file->getClientFilename();
        $ext      = $this->guessFileExt($filename);
        $path     = 'test/'.time().Str::random(16).'.'.$ext;

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
        if (false !== strrpos($name, '.')) {
            $ext = substr($name, strrpos($name, '.') + 1);
            $ext = strtolower($ext);
        }

        return $ext;
    }
}
