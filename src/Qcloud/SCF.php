<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-13 15:23:59 +0800
 */
namespace SlimExtra\Qcloud;

class SCF
{
    /**
     * @var string
     */
    protected $basePath;

    /**
     * application
     */
    protected $app;

    public function __construct(string $basePath, ?callable $callback = null)
    {
        $this->basePath = str_finish($basePath, '/');

        $app = $this->loadPhp('bootstrap/app.php');
        if (\is_callable($callback)) {
            \call_user_func($callback, $app);
        }

        $this->app = $app;
    }

    public function run($event, $context)
    {
        $request = RequestTransformer::create($event, $context);
        $response = $this->app->runWithRequest($request);
        return ResponseTransformer::mergeToArray($response);
    }

    protected function loadPhp(string $path)
    {
        $filepath = $this->basePath . $path;
        if (\file_exists($filepath)) {
            return require $filepath;
        }

        return null;
    }
}
