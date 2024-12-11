<?php

declare(strict_types = 1);

namespace Graphpinator\PersistedQueries\Tests;

use Psr\SimpleCache\CacheInterface;

final class ArrayCache implements CacheInterface
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

    public function set(string $key, mixed $value, \DateInterval|int|null $ttl = null) : bool
    {
        $this->cache[$key] = $value;
        $this->cache[$key . 'ttl'] = $ttl;

        return true;
    }

    public function delete(string $key) : bool
    {
        if (isset($this->cache[$key])) {
            unset($this->cache[$key]);

            return true;
        }

        return false;
    }

    public function clear() : bool
    {
        $this->cache = [];

        return true;
    }

    public function getMultiple(iterable $keys, mixed $default = null) : iterable
    {
        return [];
    }

    public function setMultiple(iterable $values, \DateInterval|int|null $ttl = null) : bool
    {
        return false;
    }

    public function deleteMultiple(iterable $keys) : bool
    {
        return false;
    }

    public function has($key) : bool
    {
        return isset($this->cache[$key]);
    }
}
