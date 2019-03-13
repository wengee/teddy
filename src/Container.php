<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-13 11:16:30 +0800
 */
namespace SlimExtra;

use Illuminate\Support\Collection;
use Slim\Container as SlimContainer;

class Container extends SlimContainer
{
    private $defaultSettings = [
        'httpVersion' => '1.1',
        'responseChunkSize' => 4096,
        'outputBuffering' => 'append',
        'determineRouteBeforeAppMiddleware' => false,
        'displayErrorDetails' => false,
        'addContentLengthHeader' => true,
        'routerCacheFile' => false,
    ];

    public function __construct(array $values = [])
    {
        parent::__construct($values);

        $userSettings = isset($values['settings']) ? $values['settings'] : [];
        $this->registerDefaultServices($userSettings);
    }

    private function registerDefaultServices($userSettings)
    {
        $defaultSettings = $this->defaultSettings;

        $this['settings'] = function () use ($userSettings, $defaultSettings) {
            return new Collection(array_merge($defaultSettings, $userSettings));
        };
    }

    public function call($id)
    {
        $callable = $this->raw($id);
        if (!($callable instanceof \Closure)) {
            throw new \LogicException('The service must is a Closure by the method(Container::call) call.');
        }

        return $callable($this);
    }
}
