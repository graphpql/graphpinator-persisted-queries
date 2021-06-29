<?php

declare(strict_types = 1);

namespace Graphpinator\PersistedQueries\Bench;
require 'vendor/autoload.php';

/**
 * This benchmark requires php-redis extension.
 */

$request = \Infinityloop\Utils\Json::fromNative((object) [
    'query' => 'query queryName ($var1: String = null, $var2: Int = 444, $var3: Boolean = false) { ... namedFragment } fragment namedFragment on '
        . 'Query { field { fieldArg6(arg1: $var1, arg2: $var2, arg3: $var3) @skip(if: false) } }',
]);
$type = new BenchType();
$query = new BenchQuery($type);
$container = new \Graphpinator\SimpleContainer([$query], []);
$schema = new \Graphpinator\Typesystem\Schema($container, $query);
$redis = new \Redis();
$redis->connect('localhost');
$redisCache = new RedisCache($redis);
$redisCache->clear();

$graphpinator = new \Graphpinator\Graphpinator(
    $schema,
    false,
    new \Graphpinator\Module\ModuleSet([
        new \Graphpinator\PersistedQueries\PersistedQueriesModule(
            $schema,
            $redisCache,
        ),
    ]),
);

for ($i = 0; $i < 5; $i++) {
    $graphpinator->run(new \Graphpinator\Request\JsonRequestFactory($request));
}

$startTime = \microtime(true);

for ($i = 0; $i < 10000; $i++) {
    $graphpinator->run(new \Graphpinator\Request\JsonRequestFactory($request));
}

echo 'Time: ' . \number_format((\microtime(true) - $startTime), 4) . ' Seconds' . \PHP_EOL;