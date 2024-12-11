<?php

declare(strict_types = 1);

namespace Graphpinator\PersistedQueries\Tests;

use Graphpinator\Graphpinator;
use Graphpinator\Module\ModuleSet;
use Graphpinator\PersistedQueries\PersistedQueriesModule;
use Graphpinator\Request\JsonRequestFactory;
use Graphpinator\SimpleContainer;
use Graphpinator\Typesystem\Argument\Argument;
use Graphpinator\Typesystem\Argument\ArgumentSet;
use Graphpinator\Typesystem\Container;
use Graphpinator\Typesystem\Field\ResolvableField;
use Graphpinator\Typesystem\Field\ResolvableFieldSet;
use Graphpinator\Typesystem\Schema;
use Graphpinator\Typesystem\Type;
use Infinityloop\Utils\Json;
use PHPUnit\Framework\TestCase;

final class SimpleTest extends TestCase
{
    public static function simpleDataProvider() : array
    {
        return [
            [
                Json::fromNative((object) [
                    'query' => '{ field { field { field { scalar } } } }',
                ]),
                1920335920,
                '[{"type":"query","name":null,"selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":'
                . '"field","alias":"field","argumentValueSet":[],"directiveSet":[],'
                . '"selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":"field","alias":"field",'
                . '"argumentValueSet":[],"directiveSet":[],"selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field",'
                . '"fieldName":"field","alias":"field","argumentValueSet":[],"directiveSet":[],"selectionSet":[{"selectionType":'
                . '"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":"scalar","alias":"scalar","argumentValueSet":[],'
                . '"directiveSet":[],"selectionSet":null}]}]}]}],'
                . '"variableSet":[],"directiveSet":[]}]',
                Json::fromNative((object) ['data' => ['field' => ['field' => ['field' => ['scalar' => 1]]]]]),
            ],
            [
                Json::fromNative((object) [
                    'query' => '{ field { fieldArg(arg1: 456) } }',
                ]),
                2503903000,
                '[{"type":"query","name":null,"selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":'
                . '"field","alias":"field","argumentValueSet":[],"directiveSet":[],'
                . '"selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":"fieldArg","alias":"fieldArg",'
                . '"argumentValueSet":[{"argument":"arg1",'
                . '"value":{"valueType":"Graphpinator\\\Value\\\ScalarValue","type":{"type":"named","name":"Int"},"value":456}}],"directiveSet":[],'
                . '"selectionSet":null}]}],"variableSet":[],"directiveSet":[]}]',
                Json::fromNative((object) [
                    'data' => [
                        'field' => [
                            'fieldArg' => 1,
                        ],
                    ],
                ]),
            ],
            [
                Json::fromNative((object) [
                    'query' => '{ field { fieldArg(arg1: 456) @include(if: true) @skip(if: false) } }',
                ]),
                4262924343,
                '[{"type":"query","name":null,"selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":'
                . '"field","alias":"field","argumentValueSet":[],"directiveSet":[],'
                . '"selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":"fieldArg","alias":'
                . '"fieldArg","argumentValueSet":[{"argument":"arg1","value":{"valueType":'
                . '"Graphpinator\\\Value\\\ScalarValue","type":{"type":"named","name":"Int"},"value":456}}],"directiveSet":[{"directive":'
                . '"include","arguments":[{"argument":"if","value":{"valueType":"Graphpinator\\\Value\\\ScalarValue","type":{"type":"named"'
                . ',"name":"Boolean"},"value":true}}]},{"directive":"skip","arguments":[{"argument":"if","value":{"valueType":'
                . '"Graphpinator\\\Value\\\ScalarValue","type":{"type":"named","name":"Boolean"},"value":false}}]}],"selectionSet":null'
                . '}]}],"variableSet":[],"directiveSet":[]}]',

                Json::fromNative((object) [
                    'data' => [
                        'field' => [
                            'fieldArg' => 1,
                        ],
                    ],
                ]),
            ],
            [
                Json::fromNative((object) [
                    'query' => '{ field { fieldArg(arg1: 456) @include(if: true) @skip(if: true) } }',
                ]),
                84548630,
                '[{"type":"query","name":null,"selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":'
                . '"field","alias":"field","argumentValueSet":[],"directiveSet":[],"selectionSet":'
                . '[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":"fieldArg","alias":"fieldArg",'
                . '"argumentValueSet":[{"argument":"arg1","value":{"valueType":'
                . '"Graphpinator\\\Value\\\ScalarValue","type":{"type":"named","name":"Int"},"value":456}}],"directiveSet":'
                . '[{"directive":"include","arguments":[{"argument":"if","value":{"valueType":"Graphpinator\\\Value\\\ScalarValue",'
                . '"type":{"type":"named","name":"Boolean"},"value":true}}]},{"directive":"skip","arguments":[{"argument":"if","value":{"valueType":'
                . '"Graphpinator\\\Value\\\ScalarValue","type":{"type":"named","name":"Boolean"},"value":true}}]}],'
                . '"selectionSet":null}]}],"variableSet":[],"directiveSet":[]}]',
                Json::fromNative((object) [
                    'data' => [
                        'field' => new \stdClass(),
                    ],
                ]),
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName { ... namedFragment }
                    fragment namedFragment on Query { field { field { field { scalar } } } }',
                ]),
                1205484868,
                '[{"type":"query","name":"queryName","selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\FragmentSpread",'
                . '"fragmentName":"namedFragment","selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field",'
                . '"fieldName":"field","alias":"field","argumentValueSet":[],"directiveSet":[],'
                . '"selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":"field","alias":"field",'
                . '"argumentValueSet":[],"directiveSet":[],"selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field",'
                . '"fieldName":"field","alias":"field","argumentValueSet":[],"directiveSet":[],"selectionSet":[{"selectionType":'
                . '"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":"scalar","alias":"scalar","argumentValueSet":[],'
                . '"directiveSet":[],"selectionSet":null}]}]}]}],"directiveSet":[],"typeCond":{"type":"named","name":"Query"}}],"variableSet":[],'
                . '"directiveSet":[]}]',
                Json::fromNative((object) ['data' => ['field' => ['field' => ['field' => ['scalar' => 1]]]]]),
            ],
        ];
    }

    /**
     * @param Json $request
     * @param int $crc32
     * @param string $expectedCache
     * @dataProvider simpleDataProvider
     */
    public function testSimple(Json $request, int $crc32, string $expectedCache, Json $expectedResult) : void
    {
        $container = new SimpleContainer([$this->getQuery(), $this->getType()], []);
        $schema = new Schema($container, $this->getQuery());
        $cache = [];

        $graphpinator = new Graphpinator(
            $schema,
            false,
            new ModuleSet([
                new PersistedQueriesModule(
                    $schema,
                    new ArrayCache($cache),
                ),
            ]),
        );

        $result = $graphpinator->run(new JsonRequestFactory($request));

        $this->assertArrayHasKey($crc32, $cache);
        $this->assertEquals($expectedCache, $cache[$crc32]);
        $this->assertEquals(60 * 60, $cache[$crc32 . 'ttl']);
        self::assertSame(
            $expectedResult->toString(),
            $result->toString(),
        );
    }

    /**
     * @param Json $request
     * @param int $crc32
     * @param string $expectedCache
     * @dataProvider simpleDataProvider
     */
    public function testSimpleTtl(Json $request, int $crc32, string $expectedCache, Json $expectedResult) : void
    {
        $container = new SimpleContainer([$this->getQuery(), $this->getType()], []);
        $schema = new Schema($container, $this->getQuery());
        $cache = [];

        $graphpinator = new Graphpinator(
            $schema,
            false,
            new ModuleSet([
                new PersistedQueriesModule(
                    $schema,
                    new ArrayCache($cache),
                    40 * 45,
                ),
            ]),
        );

        $result = $graphpinator->run(new JsonRequestFactory($request));

        $this->assertArrayHasKey($crc32, $cache);
        $this->assertEquals($expectedCache, $cache[$crc32]);
        $this->assertEquals(40 * 45, $cache[$crc32 . 'ttl']);
        self::assertSame(
            $expectedResult->toString(),
            $result->toString(),
        );
    }

    /**
     * @param Json $request
     * @param int $crc32
     * @param string $expectedCache
     * @dataProvider simpleDataProvider
     */
    public function testSimpleCache(Json $request, int $crc32, string $expectedCache, Json $expectedResult) : void
    {
        $container = new SimpleContainer([$this->getQuery(), $this->getType()], []);
        $schema = new Schema($container, $this->getQuery());
        $cache = [];
        $cache[$crc32] = $expectedCache;

        $graphpinator = new Graphpinator(
            $schema,
            false,
            new ModuleSet([
                new PersistedQueriesModule(
                    $schema,
                    new ArrayCache($cache),
                ),
            ]),
        );

        $result = $graphpinator->run(new JsonRequestFactory($request));

        $this->assertArrayHasKey($crc32, $cache);
        $this->assertEquals($expectedCache, $cache[$crc32]);
        self::assertSame(
            $expectedResult->toString(),
            $result->toString(),
        );
    }

    private function getQuery() : Type
    {
        return new class ($this->getType()) extends Type {
            protected const NAME = 'Query';

            public function __construct(
                private Type $type,
            )
            {
                parent::__construct();
            }

            public function validateNonNullValue(mixed $rawValue) : bool
            {
                return true;
            }

            protected function getFieldDefinition() : ResolvableFieldSet
            {
                return new ResolvableFieldSet([
                    ResolvableField::create(
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

    private function getType() : Type
    {
        return new class extends Type {
            protected const NAME = 'Type1';

            public function validateNonNullValue(mixed $rawValue) : bool
            {
                return true;
            }

            protected function getFieldDefinition() : ResolvableFieldSet
            {
                return new ResolvableFieldSet([
                    ResolvableField::create(
                        'field',
                        $this,
                        static function () : int {
                            return 1;
                        },
                    ),
                    ResolvableField::create(
                        'scalar',
                        Container::Int()->notNull(),
                        static function () : int {
                            return 1;
                        },
                    ),
                    ResolvableField::create(
                        'fieldArg',
                        Container::Int()->notNull(),
                        static function (int $parent, int $arg1) : int {
                            return 1;
                        },
                    )->setArguments(new ArgumentSet([
                        Argument::create('arg1', Container::Int())
                            ->setDefaultValue(123),
                    ])),
                ]);
            }
        };
    }
}
