<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-04-27 16:24:53 +0800
 */

namespace App\Controllers;

use App\Models\Attachment;
use App\Models\Qrcode;
use App\Tasks\Demo;
use Illuminate\Support\Str;
use Teddy\Controller;
use Teddy\Http\Request;
use Teddy\Http\Response;

class IndexController extends Controller
{
    public function index(Request $request, Response $response)
    {
        // $query = Attachment::query()
        //     ->select()
        //     ->where([
        //         ['id', 0],
        //         ['id', '>', 100],
        //         ['id', '<>', [5, 20]],
        //     ], 'OR')
        // ;

        // $b = [];
        // $a = $query->getSql($b);

        // // $c = new Qrcode;
        // $c            = $query->first();
        // $c['isImage'] = true;
        // $c->save();

        $task = new Demo();
        $task->queue();

        return $response->json(0);
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
