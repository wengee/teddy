<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-18 17:34:50 +0800
 */

namespace App\Controllers;

use App\Models\Abc;
use App\Tasks\Demo;
use Illuminate\Support\Str;
use Teddy\Controller;
use Teddy\Http\Request;
use Teddy\Http\Response;

class IndexController extends Controller
{
    public function index(Request $request, Response $response)
    {
        echo time().PHP_EOL;
        $model = Abc::create('2');
        $model = $model->save() ? $model : null;

        run_task(Demo::class, [], ['delay' => 2]);
        // app('redis')->lPush('abc', 'fdsafsadfasd');

        $list = Abc::query('1')->orderBy('id', 'DESC')->limit(3)->all();
        $list = array_map(function ($item) {
            /**
             * @var Abc $item
             */
            $data = $item->toArray();

            $timeslot = $item['timeslot'] ?? null;
            if ($timeslot) {
                $data['timeslot'] = $timeslot->setTimeZone(timezone_open('+0700'))->format('c');
            }

            return [
                'data'        => $data,
                'isNewRecord' => $item->isNewRecord(),
            ];
        }, $list);

        return $response->json(0, [
            'model' => [
                'data'        => $model,
                'isNewRecord' => $model->isNewRecord(),
            ],
            'list' => $list,
            'cpu'  => teddy_cpu_num(),
            'body' => (string) $request->getBody(),
            'post' => $request->getParsedBody(),
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
