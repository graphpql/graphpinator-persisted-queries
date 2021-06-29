# GraPHPinator Persisted Queries [![PHP](https://github.com/infinityloop-dev/graphpinator-persisted-queries/workflows/PHP/badge.svg?branch=master)](https://github.com/infinityloop-dev/graphpinator-persisted-queries/actions?query=workflow%3APHP) [![codecov](https://codecov.io/gh/infinityloop-dev/graphpinator-persisted-queries/branch/master/graph/badge.svg)](https://codecov.io/gh/infinityloop-dev/graphpinator-persisted-queries)

:zap::globe_with_meridians::zap: Module to persist validated query in cache and improve performace of repeating queries.

## Introduction

This Module allows GraPHPinator to cache queries on the server to reduce server load. 

## Installation

Install package using composer

```composer require infinityloop-dev/graphpinator-persisted-queries```

## How to use

1. Implement `\Psr\SimpleCache\CacheInterface`

You need implementation of `\Psr\SimpleCache\CacheInterface` where the serialized version of request is stored for later reuse.

2. Register `PersistedQueriesModule` as GraPHPinator module:

```php
$persistModule = new \Graphpinator\PersistedQueriesModule\PersistedQueriesModule($schema, $cacheImpl);
$graphpinator = new \Graphpinator\Graphpinator(
    $schema,
    $catchExceptions,
    new \Graphpinator\Module\ModuleSet([$persistModule, /* possibly other modules */]),
    $logger,
);
```

3. You are all set, queries are automatically cached in specified storage.

## Performance inprovements

Simple benchmark (code in `bench` directory) shows approximatelly 80% reduction of GraphQL overhead.

Benchmark runs the same query 10k times, with 5 warmup queries before. Cache in use is a simple implementation using Redis on localhost, connection is done using php-redis extension.

| CPU model | Time WITHOUT module | Time WITH module |
| --------- | ------------------- | ---------------- |
| Ryzen 5900X | ~12.44 s | ~2.16 s |
