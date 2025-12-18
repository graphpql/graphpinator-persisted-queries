<?php

declare(strict_types = 1);

namespace Graphpinator\PersistedQueries\Bench;

use Psr\SimpleCache\CacheInterface;

class RedisCache implements CacheInterface {

    protected \Redis $redis;

    /**
     * RedisCache constructor.
     */
    public function __construct(\Redis $redis){
        $this->redis = $redis;
    }

    protected function checkKey($key) : string {
        return (string) $key;
    }

    protected function getTTL($ttl) : ?int
    {
        if ((\is_int($ttl) && $ttl > 0) || $ttl === null) {
            return $ttl;
        }

        throw new \InvalidArgumentException('Invalid TTL value.');
    }

    public function has($key) : bool {
        return $this->get((string) $key) !== null;
    }

    public function get($key, $default = null) {
        $value = $this->redis->get($this->checkKey((string) $key));

        if($value !== false){
            return $value;
        }

        return $default;
    }

    public function set($key, $value, $ttl = null) : bool{
        $key = (string) $key;
        $key = $this->checkKey($key);
        $ttl = $this->getTTL($ttl);

        if ($ttl === null){
            return $this->redis->set($key, $value);
        }

        return $this->redis->setex($key, $ttl, $value);
    }

    public function clear() : bool {
        return $this->redis->flushDB();
    }

    public function delete($key)
    {
    }

    public function getMultiple($keys, $default = null)
    {
    }

    public function setMultiple($values, $ttl = null)
    {
    }

    public function deleteMultiple($keys)
    {
    }
}