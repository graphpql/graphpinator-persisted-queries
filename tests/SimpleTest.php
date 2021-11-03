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
                '[{"type":"query","name":null,"selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":'
                . '"field","alias":"field","argumentValueSet":[],"directiveSet":[],'
                . '"selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":"field","alias":"field",'
                . '"argumentValueSet":[],"directiveSet":[],"selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field",'
                . '"fieldName":"field","alias":"field","argumentValueSet":[],"directiveSet":[],"selectionSet":[{"selectionType":'
                . '"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":"scalar","alias":"scalar","argumentValueSet":[],'
                . '"directiveSet":[],"selectionSet":null}]}]}]}],'
                . '"variableSet":[],"directiveSet":[]}]',
                \Infinityloop\Utils\Json::fromNative((object) ['data' => ['field' => ['field' => ['field' => ['scalar' => 1]]]]]),
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
                \Infinityloop\Utils\Json::fromNative((object) [
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

                \Infinityloop\Utils\Json::fromNative((object) [
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
                \Infinityloop\Utils\Json::fromNative((object) [
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
                \Infinityloop\Utils\Json::fromNative((object) ['data' => ['field' => ['field' => ['field' => ['scalar' => 1]]]]]),
            ],
        ];
    }

    /**
     * @param \Infinityloop\Utils\Json $request
     * @param int $crc32
     * @param string $expectedCache
     * @dataProvider simpleDataProvider
     */
    public function testSimple(Json $request, int $crc32, string $expectedCache, Json $expectedResult) : void
    {
        $container = new \Graphpinator\SimpleContainer([$this->getQuery(), $this->getType()], []);
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
            ]),
        );

        $result = $graphpinator->run(new \Graphpinator\Request\JsonRequestFactory($request));

        $this->assertArrayHasKey($crc32, $cache);
        $this->assertEquals($expectedCache, $cache[$crc32]);
        self::assertSame(
            $expectedResult->toString(),
            $result->toString(),
        );
    }

    /**
     * @param \Infinityloop\Utils\Json $request
     * @param int $crc32
     * @param string $expectedCache
     * @dataProvider simpleDataProvider
     */
    public function testSimpleCache(Json $request, int $crc32, string $expectedCache, Json $expectedResult) : void
    {
        $container = new \Graphpinator\SimpleContainer([$this->getQuery(), $this->getType()], []);
        $schema = new \Graphpinator\Typesystem\Schema($container, $this->getQuery());
        $cache = [];
        $cache[$crc32] = $expectedCache;

        $graphpinator = new \Graphpinator\Graphpinator(
            $schema,
            false,
            new \Graphpinator\Module\ModuleSet([
                new \Graphpinator\PersistedQueries\PersistedQueriesModule(
                    $schema,
                    new \Graphpinator\PersistedQueries\Tests\ArrayCache($cache),
                ),
            ]),
        );

        $result = $graphpinator->run(new \Graphpinator\Request\JsonRequestFactory($request));

        $this->assertArrayHasKey($crc32, $cache);
        $this->assertEquals($expectedCache, $cache[$crc32]);
        self::assertSame(
            $expectedResult->toString(),
            $result->toString(),
        );
    }

    private function getQuery() : \Graphpinator\Typesystem\Type
    {
        return new class ($this->getType()) extends \Graphpinator\Typesystem\Type {
            protected const NAME = 'Query';

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
            protected const NAME = 'Type1';

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
                        \Graphpinator\Typesystem\Container::Int()->notNull(),
                        static function () : int {
                            return 1;
                        },
                    ),
                    \Graphpinator\Typesystem\Field\ResolvableField::create(
                        'fieldArg',
                        \Graphpinator\Typesystem\Container::Int()->notNull(),
                        static function (int $parent, int $arg1) : int {
                            return 1;
                        },
                    )->setArguments(new \Graphpinator\Typesystem\Argument\ArgumentSet([
                        \Graphpinator\Typesystem\Argument\Argument::create('arg1', \Graphpinator\Typesystem\Container::Int())
                            ->setDefaultValue(123),
                    ])),
                ]);
            }
        };
    }
}
