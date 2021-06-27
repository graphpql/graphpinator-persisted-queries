<?php

declare(strict_types = 1);

namespace Graphpinator\PersistedQueries;

class PersistedQueriesModule implements \Graphpinator\Module\Module
{
    use \Nette\SmartObject;

    private int $queryHash;

    public function __construct(
        private \Graphpinator\Typesystem\Schema $schema,
        private \Psr\SimpleCache\CacheInterface $cache,
        private int $ttl = 60 * 60,
    )
    {
    }

    public function processRequest(\Graphpinator\Request\Request $request) : \Graphpinator\Request\Request|\Graphpinator\Normalizer\NormalizedRequest
    {
        $this->queryHash = \crc32($request->getQuery());

        $cache = $this->cache->get($this->queryHash);

        if ($cache !== null) {
            $deserializer = new Deserializer($this->schema);

            return $deserializer->deserializeNormalizedRequest($cache);
        }

        return $request;
    }

    public function processParsed(\Graphpinator\Parser\ParsedRequest $request) : \Graphpinator\Parser\ParsedRequest
    {
        return $request;
    }

    public function processNormalized(\Graphpinator\Normalizer\NormalizedRequest $request) : \Graphpinator\Normalizer\NormalizedRequest
    {
        $serializer = new Serializer();

        $this->cache->set($this->queryHash, $serializer->serializeNormalizedRequest($request), $this->ttl);

        return $request;
    }

    public function processFinalized(\Graphpinator\Normalizer\FinalizedRequest $request) : \Graphpinator\Normalizer\FinalizedRequest
    {
        return $request;
    }

    public function processResult(\Graphpinator\Result $result) : \Graphpinator\Result
    {
        return $result;
    }
}
