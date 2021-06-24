<?php

declare(strict_types = 1);

namespace Graphpinator\PersistedQueries\Tests;

use \Infinityloop\Utils\Json;

final class SimpleTest extends \PHPUnit\Framework\TestCase
{
    public function simpleDataProvider() : array
    {
        return [
            [
                Json::fromNative((object) [
                    'query' => '{ field { field { field { scalar } } } }',
                ]),
                1920335920,
                '[{"type":"query","name":null,"fieldSet":[{"fieldName":"field","alias":"field","argumentValueSet":[],"directiveSet":[],'
                . '"fieldSet":[{"fieldName":"field","alias":"field","argumentValueSet":[],"directiveSet":[],"fieldSet":[{"fieldName":"field",'
                . '"alias":"field","argumentValueSet":[],"directiveSet":[],"fieldSet":[{"fieldName":"scalar","alias":"scalar","argumentValueSet":[],'
                . '"directiveSet":[],"fieldSet":null,"typeCond":null}],"typeCond":null}],"typeCond":null}],"typeCond":null}],'
                . '"variableSet":[],"directiveSet":[]}]'
            ]
        ];
    }

    /**
     * @param Json $request
     * @param int $crc32
     * @param string $expectedCache
     * @dataProvider simpleDataProvider
     */
    public function testSimple(Json $request, int $crc32, string $expectedCache) : void
    {
        $container = new \Graphpinator\SimpleContainer([$this->getQuery()], []);
        $schema = new \Graphpinator\Typesystem\Schema($container, $this->getQuery());
        $cache = [];

        $graphpinator = new \Graphpinator\Graphpinator(
            $schema,
            false,
            new \Graphpinator\Module\ModuleSet([
                new \Graphpinator\PersistedQueries\PersistedQueriesModule(
                    $schema,
                    new \Graphpinator\PersistedQueries\Tests\ArrayCache($cache),
                ),
            ])
        );

        $graphpinator->run(new \Graphpinator\Request\JsonRequestFactory($request));

        $this->assertArrayHasKey($crc32, $cache);
        $this->assertEquals($expectedCache, $cache[$crc32]);
    }

    /**
     * @param Json $request
     * @param int $crc32
     * @param string $expectedCache
     * @dataProvider simpleDataProvider
     */
    public function testSimpleCache(Json $request, int $crc32, string $expectedCache) : void
    {
        $container = new \Graphpinator\SimpleContainer([$this->getQuery()], []);
        $schema = new \Graphpinator\Typesystem\Schema($container, $this->getQuery());
        $cache[$crc32] = $expectedCache;

        $graphpinator = new \Graphpinator\Graphpinator(
            $schema,
            false,
            new \Graphpinator\Module\ModuleSet([
                new \Graphpinator\PersistedQueries\PersistedQueriesModule(
                    $schema,
                    new \Graphpinator\PersistedQueries\Tests\ArrayCache($cache),
                ),
            ])
        );

        $graphpinator->run(new \Graphpinator\Request\JsonRequestFactory($request));

        $this->assertArrayHasKey($crc32, $cache);
        $this->assertEquals($expectedCache, $cache[$crc32]);
    }

    private function getQuery() : \Graphpinator\Typesystem\Type
    {
        return new class ($this->getType()) extends \Graphpinator\Typesystem\Type {
            public function __construct(
                private \Graphpinator\Typesystem\Type $type,
            )
            {
                parent::__construct();
            }

            public function validateNonNullValue(mixed $rawValue) : bool
            {
                return true;
            }

            protected function getFieldDefinition() : \Graphpinator\Typesystem\Field\ResolvableFieldSet
            {
                return new \Graphpinator\Typesystem\Field\ResolvableFieldSet([
                    \Graphpinator\Typesystem\Field\ResolvableField::create(
                        'field',
                        $this->type->notNull(),
                        static function () : int {
                            return 1;
                        },
                    ),
                ]);
            }
        };
    }

    private function getType() : \Graphpinator\Typesystem\Type
    {
        return new class extends \Graphpinator\Typesystem\Type {
            public function validateNonNullValue(mixed $rawValue) : bool
            {
                return true;
            }

            protected function getFieldDefinition() : \Graphpinator\Typesystem\Field\ResolvableFieldSet
            {
                return new \Graphpinator\Typesystem\Field\ResolvableFieldSet([
                    \Graphpinator\Typesystem\Field\ResolvableField::create(
                        'field',
                        $this,
                        static function () : int {
                            return 1;
                        },
                    ),
                    \Graphpinator\Typesystem\Field\ResolvableField::create(
                        'scalar',
                        \Graphpinator\Container\Container::Int()->notNull(),
                        static function () : int {
                            return 1;
                        },
                    ),
                ]);
            }
        };
    }
}
