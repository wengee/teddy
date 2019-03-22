<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-22 14:26:50 +0800
 */
namespace Teddy;

use Monolog\Handler\AbstractHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\PsrLogMessageProcessor;
use Slim\DefaultServicesProvider as SlimDefaultServicesProvider;
use Slim\Http\Headers;
use Teddy\Http\Request;
use Teddy\Http\Response;
use Teddy\Validation\Validation;

class DefaultServicesProvider extends SlimDefaultServicesProvider
{
    public function register($container)
    {
        $settings = $container['settings'];

        $requestClass = $settings->get('requestClass', Request::class);
        $container['request'] = function ($c) use ($requestClass) {
            return $requestClass::createFromEnvironment($c->get('environment'));
        };

        $responseClass = $settings->get('responseClass', Response::class);
        $container['response'] = function ($c) use ($responseClass) {
            $headers = new Headers(['Content-Type' => 'text/html; charset=UTF-8']);
            $response = new $responseClass(200, $headers);

            return $response->withProtocolVersion($c->get('settings')['httpVersion']);
        };

        $container['callableResolver'] = function ($c) {
            return new CallableResolver($c);
        };

        $container['router'] = function ($c) {
            $routerCacheFile = false;
            if (isset($c->get('settings')['routerCacheFile'])) {
                $routerCacheFile = $c->get('settings')['routerCacheFile'];
            }


            $router = (new Router)->setCacheFile($routerCacheFile);
            if (method_exists($router, 'setContainer')) {
                $router->setContainer($c);
            }

            return $router;
        };

        $container['logger'] = function ($c) {
            $settings = (array) $c['settings']->get('logger');

            $name = array_get($settings, 'name', 'slim');
            $processors = [
                new PsrLogMessageProcessor,
                new MemoryUsageProcessor,
                new MemoryPeakUsageProcessor,
            ];

            $handlerObjs = [];
            $handlers = (array) array_get($settings, 'handlers');
            $level = array_get($settings, 'level') ?: 'DEBUG';
            foreach ($handlers as $key => $value) {
                if (is_subclass_of($value, AbstractHandler::class)) {
                    $handlerObjs[] = is_object($value) ?: new $value;
                } elseif ($key === 'file') {
                    $handlerObjs[] = new StreamHandler($value, $level);
                }
            }

            return new Logger($name, $handlerObjs, $processors);
        };

        $container['filter'] = new Filter;
        $container['validation'] = new Validation;

        parent::register($container);
    }
}
