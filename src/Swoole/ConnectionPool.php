<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-06 15:32:19 +0800
 */
namespace SlimExtra\Swoole;

use Swoole\Coroutine\Channel;

class ConnectionPool
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var callable
     */
    protected $newInstance;

    /**
     * @var array
     */
    protected $instances = [];

    /**
     * @var Channel
     */
    protected $pool;

    /**
     * @var integer
     */
    protected $poolSize = 1;

    public function __construct(array $options, ?callable $newInstance = null)
    {
        $this->options = $options + $this->getDefaultOptions();
        $this->poolSize = (int) $this->options['poolSize'];
        $this->pool = new Channel($this->poolSize);
        if ($newInstance !== null) {
            $this->setNewInstance($newInstance);
        }

        // $this->createNewInstance($this->poolSize);
    }

    public function setNewInstance(callable $newInstance)
    {
        $this->newInstance = $newInstance;
        return $this;
    }

    public function get()
    {
        if ($this->pool->isEmpty()) {
            $this->createNewInstance();
        }

        return $this->pool->pop();
    }

    public function put($instance)
    {
        $instanceId = \spl_object_hash($instance);
        if (isset($this->instances[$instanceId])) {
            $this->pool->push($instance);
        }
    }

    public function renew($instance)
    {
        $instanceId = \spl_object_hash($instance);
        if (isset($this->instances[$instanceId])) {
            unset($this->instances[$instanceId]);
        }

        unset($instance);
        $instance = $this->createNewInstance(1, true);
        return $instance ?: $this->get();
    }

    protected function createNewInstance(int $size = 1, bool $return = false)
    {
        if (count($this->instances) >= $this->poolSize) {
            return false;
        }

        $n = 0;
        while ($n < $size) {
            $instance = \call_user_func($this->newInstance);
            $instanceId = \spl_object_hash($instance);
            if (count($this->instances) < $this->poolSize) {
                $this->instances[$instanceId] = true;

                if ($return) {
                    return $instance;
                } else {
                    $this->pool->push($instance);
                }
            } else {
                break;
            }

            $n += 1;
        }
    }

    protected function getDefaultOptions(): array
    {
        return [
            'poolSize' => 10,
        ];
    }
}
