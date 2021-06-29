<?php

declare(strict_types = 1);

namespace Graphpinator\PersistedQueries\Bench;

class RedisCache implements \Psr\SimpleCache\CacheInterface {

    protected \Redis $redis;

    /**
     * RedisCache constructor.
     */
    public function __construct(\Redis $redis){
        $this->redis = $redis;
    }

    protected function checkKey($key):string{
        $key = (string) $key;
        if(!\is_string($key) || empty($key)){
            throw new \InvalidArgumentException('invalid cache key: "'.$key.'"');
        }

        return $key;
    }

    protected function checkKeyArray(array $keys):void{
        foreach($keys as $key){
            $key = (string) $key;
            $this->checkKey($key);
        }
    }

    protected function getData($data):array{
        if (\is_array($data)) {
            return $data;
        }
        elseif ($data instanceof \Traversable) {
            return iterator_to_array($data); // @codeCoverageIgnore
        }

        throw new \InvalidArgumentException('invalid data');
    }

    protected function getTTL($ttl):?int{
        if($ttl instanceof \DateInterval){
            return (new \DateTime)->add($ttl)->getTimeStamp() - time();
        }
        else if((is_int($ttl) && $ttl > 0) || $ttl === null){
            return $ttl;
        }

        throw new \InvalidArgumentException('invalid ttl');
    }

    public function has($key) : bool {
        $key = (string) $key;
        return $this->get($key) !== null;
    }

    public function get($key, $default = null) {
        $key = (string) $key;
        $value = $this->redis->get($this->checkKey($key));

        if($value !== false){
            return $value;
        }

        return $default;
    }

    /** @inheritdoc */
    public function set($key, $value, $ttl = null):bool{
        $key = (string) $key;
        $key = $this->checkKey($key);
        $ttl = $this->getTTL($ttl);

        if($ttl === null){
            return $this->redis->set($key, $value);
        }

        return $this->redis->setex($key, $ttl, $value);
    }

    protected function checkReturn(array $booleans) : bool
    {
        foreach($booleans as $boolean){
            if(!(bool)$boolean){
                return false; // @codeCoverageIgnore
            }
        }

        return true;
    }

    /** @inheritdoc */
    public function delete($key):bool{
        $key = (string) $key;
        return (bool)$this->redis->del($this->checkKey($key));
    }

    /** @inheritdoc */
    public function clear():bool{
        return $this->redis->flushDB();
    }

    /** @inheritdoc */
    public function getMultiple($keys, $default = null):array{
        $keys = $this->getData($keys);

        $this->checkKeyArray($keys);

        // scary
        $values = array_combine($keys, $this->redis->mget($keys));
        $return = [];

        foreach($keys as $key){
            $key = (string) $key;
            /** @phan-suppress-next-line PhanTypeArraySuspiciousNullable */
            $return[$key] = $values[$key] !== false ? $values[$key] : $default;
        }

        return $return;
    }

    /** @inheritdoc */
    public function setMultiple($values, $ttl = null) : int
    {
        $values = $this->getData($values);
        $ttl    = $this->getTTL($ttl);

        if($ttl === null){
            $this->checkKeyArray(array_keys($values));

            return $this->redis->msetnx($values);
        }

        $return = [];

        foreach($values as $key => $value){
            $return[] = $this->set($key, $value, $ttl);
        }

        return $this->checkReturn($return) === true
            ? 1
            : 0;
    }

    /** @inheritdoc */
    public function deleteMultiple($keys):bool{
        $keys = $this->getData($keys);

        $this->checkKeyArray($keys);

        return (bool)$this->redis->del($keys);
    }
}