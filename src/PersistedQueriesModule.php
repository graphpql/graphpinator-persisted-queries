<?php

declare(strict_types = 1);

namespace Graphpinator\PersistedQueries;

use Graphpinator\Module\Module;
use Graphpinator\Normalizer\FinalizedRequest;
use Graphpinator\Normalizer\NormalizedRequest;
use Graphpinator\Parser\ParsedRequest;
use Graphpinator\Request\Request;
use Graphpinator\Resolver\Result;
use Graphpinator\Typesystem\Schema;
use Psr\SimpleCache\CacheInterface;

class PersistedQueriesModule implements Module
{
    private string $queryHash;

    public function __construct(
        private Schema $schema,
        private CacheInterface $cache,
        private int $ttl = 60 * 60,
    )
    {
    }

    #[\Override]
    public function processRequest(Request $request) : Request|NormalizedRequest
    {
        $this->queryHash = (string) \crc32($request->query);

        $cache = $this->cache->get($this->queryHash);

        if ($cache !== null) {
            $deserializer = new Deserializer($this->schema);

            return $deserializer->deserializeNormalizedRequest($cache);
        }

        return $request;
    }

    #[\Override]
    public function processParsed(ParsedRequest $request) : ParsedRequest
    {
        return $request;
    }

    #[\Override]
    public function processNormalized(NormalizedRequest $request) : NormalizedRequest
    {
        $serializer = new Serializer();

        $this->cache->set($this->queryHash, $serializer->serializeNormalizedRequest($request), $this->ttl);

        return $request;
    }

    #[\Override]
    public function processFinalized(FinalizedRequest $request) : FinalizedRequest
    {
        return $request;
    }

    #[\Override]
    public function processResult(Result $result) : Result
    {
        return $result;
    }
}
