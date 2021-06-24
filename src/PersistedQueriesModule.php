<?php

declare(strict_types=1);

namespace Graphpinator\PersistedQueries;

class PersistedQueriesModule implements \Graphpinator\Module\Module
{
    use \Nette\SmartObject;

    private string $queryHash;

    //Psr\SimpleCache\CacheInterface
    //Možná použít https://github.com/laminas/laminas-cache/ až se bude zase testovat, redis byl na píču
    public function __construct(
        private \Graphpinator\Typesystem\Schema $schema,
    )
    {

    }

    public function processRequest(\Graphpinator\Request\Request $request) : \Graphpinator\Request\Request|\Graphpinator\Normalizer\NormalizedRequest
    {
        // todo: find in cache

        /*
         * $this->queryHash = \crc32($request->getQuery());
         * $cache = $this->cache->get($this->queryHash);
         *
         * if ($cache !== null) {
         *  $deserializer = new Deserializer($this->schema);
         *
         *  return $deserializer->deserializeNormalizedRequest($cache);
         * }
         *
         */

        return $request;
    }

    public function processParsed(\Graphpinator\Parser\ParsedRequest $request) : \Graphpinator\Parser\ParsedRequest
    {
        return $request;
    }

    public function processNormalized(\Graphpinator\Normalizer\NormalizedRequest $request) : \Graphpinator\Normalizer\NormalizedRequest
    {
        $serializer = new Serializer();
        $serialized = $serializer->serializeNormalizedRequest($request);

        /*
         * $cache = $this->cache->get($this->queryHash);
         *
         * if ($cache !== null) {
         *  return $request;
         * }
         *
         * $this->cache->set($this->queryHash, $serialized, 60 * 60);
         */

        return $request;
    }

    public function processFinalized(\Graphpinator\Normalizer\FinalizedRequest $request) : \Graphpinator\Normalizer\FinalizedRequest
    {
        return $request;
    }
}