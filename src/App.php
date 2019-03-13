<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-13 15:12:29 +0800
 */
namespace SlimExtra;

use Pimple\ServiceProviderInterface;
use Slim\App as SlimApp;
use Slim\Http\Request;
use Slim\Http\Response;

class App extends SlimApp
{
    /**
     * @var string
     */
    protected $basePath;

    /**
     * @var App
     */
    protected static $instance;

    public function __construct(string $basePath, $defaultServicesProvider = null)
    {
        static::$instance = $this;
        $this->basePath = $basePath = str_finish($basePath, '/');
        $container = [];

        $container['settings'] = $this->loadSettings();
        $container = new Container($container);
        if ($defaultServicesProvider === null) {
            $defaultServicesProvider = new DefaultServicesProvider;
        }
        $defaultServicesProvider->register($container);

        parent::__construct($container);
        $this->loadRoutes();
    }

    public static function instance()
    {
        if (!isset(static::$instance)) {
            throw new \Exception('App is not exists.');
        }

        return static::$instance;
    }

    public function runWithRequest(Request $request): Response
    {
        $response = $this->getContainer()->call('response');

        try {
            $response = $this->process($request, $response);
        } catch (InvalidMethodException $e) {
            $response = $this->processInvalidMethod($e->getRequest(), $response);
        }

        $response = $this->finalize($response);
        return $response;
    }

    public function register(ServiceProviderInterface $provider, array $values = [])
    {
        $this->getContainer()->register($provider, $values);
    }

    public function group($pattern, $callable)
    {
        if (is_callable($pattern)) {
            $callable = $pattern;
            $pattern = '';
        }

        return parent::group($pattern, $callable);
    }

    protected function loadSettings(): array
    {
        $settings = [];
        $dir = $this->basePath . 'config/';

        if (is_dir($dir)) {
            $handle = opendir($dir);
            while (false !== ($file = readdir($handle))) {
                $filepath = $dir . $file;
                if ($file === 'app.php') {
                    $data = (array) require $filepath;
                    $settings = array_merge($settings, $data);
                } elseif (ends_with($file, '.php') && is_file($filepath)) {
                    $name = substr($file, 0, -4);
                    $settings[$name] = require $filepath;
                }
            }
        }

        return $settings;
    }

    protected function loadRoutes()
    {
        $dir = $this->basePath . 'routes/';
        $this->group(['namespace' => '\\App\\Controllers'], function ($app) use ($dir) {
            if (is_dir($dir)) {
                $handle = opendir($dir);
                while (false !== ($file = readdir($handle))) {
                    $filepath = $dir . $file;
                    if (ends_with($file, '.php') && is_file($filepath)) {
                        require $filepath;
                    }
                }
            }
        });
    }
}
