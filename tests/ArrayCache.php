<?php

declare(strict_types = 1);

namespace Graphpinator\PersistedQueries\Tests;

final class ArrayCache implements \Psr\SimpleCache\CacheInterface
{
    public function __construct(
        private array &$cache = [],
    )
    {
    }

    public function get($key, $default = null) : mixed
    {
        if (!isset($this->cache[$key])) {
            return $default;
        }

        return $this->cache[$key];
    }

    public function set($key, $value, $ttl = null) : void
    {
        $this->cache[$key] = $value;
    }

    public function delete($key) : void
    {
        if (isset($this->cache[$key])) {
            unset($this->cache[$key]);
        }
    }

    public function clear() : void
    {
        $this->cache = [];
    }

    public function getMultiple($keys, $default = null) : void
    {
    }

    public function setMultiple($values, $ttl = null) : void
    {
    }

    public function deleteMultiple($keys) : void
    {
    }

    public function has($key) : bool
    {
        return isset($this->cache[$key]);
    }
}
