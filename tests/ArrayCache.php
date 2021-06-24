<?php

declare(strict_types = 1);

namespace Graphpinator\PersistedQueries\Tests;

final class ArrayCache implements \Psr\SimpleCache\CacheInterface
{
    public function __construct(
        private array &$cache = []
    )
    {
    }

    public function get($key, $default = null)
    {
        if (!isset($this->cache[$key])) {
            return $default;
        }

        return $this->cache[$key];
    }

    public function set($key, $value, $ttl = null)
    {
        $this->cache[$key] = $value;
    }

    public function delete($key)
    {
        if (isset($this->cache[$key])) {
            unset($this->cache[$key]);
        }
    }

    public function clear()
    {
        $this->cache = [];
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

    public function has($key)
    {
        return isset($this->cache[$key]);
    }
}
