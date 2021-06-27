<?php

declare(strict_types = 1);

namespace Graphpinator\PersistedQueries\Tests;

use \Infinityloop\Utils\Json;

final class OperationTest extends \PHPUnit\Framework\TestCase
{
    public static function getType2() : \Graphpinator\Typesystem\Type
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
                        OperationTest::getType(),
                        static function () : int {
                            return 123;
                        },
                    ),
                ]);
            }
        };
    }

    public static function getType() : \Graphpinator\Typesystem\Type
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
                            return 123;
                        },
                    ),
                    \Graphpinator\Typesystem\Field\ResolvableField::create(
                        'scalar',
                        \Graphpinator\Container\Container::Int()->notNull(),
                        static function () : int {
                            return 987;
                        },
                    ),
                    \Graphpinator\Typesystem\Field\ResolvableField::create(
                        'fieldArg',
                        \Graphpinator\Container\Container::Int()->notNull(),
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

    public function simpleDataProvider() : array
    {
        return [
            [
                Json::fromNative((object) [
                    'query' => 'query queryName { field { scalar } } query queryName2 { field { fieldArg } }',
                ]),
                1485165402,
                '[{"type":"query","name":"queryName","fieldSet":[{"fieldName":"field","alias":"field","argumentValueSet":[],"directiveSet":[],'
                . '"fieldSet":[{"fieldName":"scalar","alias":"scalar","argumentValueSet":[],"directiveSet":[],"fieldSet":null,"typeCond":null}],'
                . '"typeCond":null}],"variableSet":[],"directiveSet":[]},{"type":"query","name":"queryName2","fieldSet":[{"fieldName":"field",'
                . '"alias":"field","argumentValueSet":[],"directiveSet":[],"fieldSet":[{"fieldName":"fieldArg","alias":"fieldArg","argumentValueSet":'
                . '[{"argument":"arg1","value":{"valueType":"Graphpinator\\\Value\\\ScalarValue","type":{"type":"named","name":"Int"},"value":123}}],'
                . '"directiveSet":[],"fieldSet":null,"typeCond":null}],"typeCond":null}],"variableSet":[],"directiveSet":[]}]',
                \Infinityloop\Utils\Json::fromNative((object) ['data' => ['field' => ['scalar' => 987]]]),
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName { field { scalar } } query queryName2 { field2 { field { fieldArg } } }',
                ]),
                1589897943,
                '[{"type":"query","name":"queryName","fieldSet":[{"fieldName":"field","alias":"field","argumentValueSet":[],"directiveSet":[],'
                . '"fieldSet":[{"fieldName":"scalar","alias":"scalar","argumentValueSet":[],"directiveSet":[],"fieldSet":null,"typeCond":null}],'
                . '"typeCond":null}],"variableSet":[],"directiveSet":[]},{"type":"query","name":"queryName2","fieldSet":[{"fieldName":"field2",'
                . '"alias":"field2","argumentValueSet":[],"directiveSet":[],"fieldSet":[{"fieldName":"field","alias":"field","argumentValueSet":[]'
                . ',"directiveSet":[],"fieldSet":[{"fieldName":"fieldArg","alias":"fieldArg","argumentValueSet":[{"argument":"arg1","value":'
                . '{"valueType":"Graphpinator\\\Value\\\ScalarValue","type":{"type":"named","name":"Int"},"value":123}}],"directiveSet":[],'
                . '"fieldSet":null,"typeCond":null}],"typeCond":null}],"typeCond":null}],"variableSet":[],"directiveSet":[]}]',
                \Infinityloop\Utils\Json::fromNative((object) ['data' => ['field' => ['scalar' => 987]]]),
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName { field { scalar } } mutation mutationName { mutationField, secondField: mutationField, '
                        . 'thirdField: mutationField }',
                ]),
                2364436310,
                '[{"type":"query","name":"queryName","fieldSet":[{"fieldName":"field","alias":"field","argumentValueSet":[],"directiveSet":[],'
                . '"fieldSet":[{"fieldName":"scalar","alias":"scalar","argumentValueSet":[],"directiveSet":[],"fieldSet":null,"typeCond":null}],'
                . '"typeCond":null}],"variableSet":[],"directiveSet":[]},{"type":"mutation","name":"mutationName","fieldSet":[{"fieldName":'
                . '"mutationField","alias":"mutationField","argumentValueSet":[],"directiveSet":[],"fieldSet":null,"typeCond":null},{"fieldName"'
                . ':"mutationField","alias":"secondField","argumentValueSet":[],"directiveSet":[],"fieldSet":null,"typeCond":null},{"fieldName":'
                . '"mutationField","alias":"thirdField","argumentValueSet":[],"directiveSet":[],"fieldSet":null,"typeCond":null}],"variableSet":[]'
                . ',"directiveSet":[]}]',
                \Infinityloop\Utils\Json::fromNative((object) ['data' => ['field' => ['scalar' => 987]]]),
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName { field { scalar } } mutation mutationName { mutationField, secondField:'
                        . 'mutationField, thirdField: mutationField } subscription subscriptionName { subscriptionField }',
                ]),
                1309780448,
                '[{"type":"query","name":"queryName","fieldSet":[{"fieldName":"field","alias":"field","argumentValueSet":[],"directiveSet":[],'
                . '"fieldSet":[{"fieldName":"scalar","alias":"scalar","argumentValueSet":[],"directiveSet":[],"fieldSet":null,"typeCond":null}],'
                . '"typeCond":null}],"variableSet":[],"directiveSet":[]},{"type":"mutation","name":"mutationName","fieldSet":[{"fieldName":'
                . '"mutationField","alias":"mutationField","argumentValueSet":[],"directiveSet":[],"fieldSet":null,"typeCond":null},{"fieldName"'
                . ':"mutationField","alias":"secondField","argumentValueSet":[],"directiveSet":[],"fieldSet":null,"typeCond":null},{"fieldName":'
                . '"mutationField","alias":"thirdField","argumentValueSet":[],"directiveSet":[],"fieldSet":null,"typeCond":null}],"variableSet":[]'
                . ',"directiveSet":[]},{"type":"subscription","name":"subscriptionName","fieldSet":[{"fieldName":"subscriptionField","alias":'
                . '"subscriptionField","argumentValueSet":[],"directiveSet":[],"fieldSet":null,"typeCond":null}],"variableSet":[],'
                . '"directiveSet":[]}]',
                \Infinityloop\Utils\Json::fromNative((object) ['data' => ['field' => ['scalar' => 987]]]),
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
        $container = new \Graphpinator\SimpleContainer([$this->getQuery()], []);
        $schema = new \Graphpinator\Typesystem\Schema($container, $this->getQuery(), $this->getMutation(), $this->getSubscription());
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
        $container = new \Graphpinator\SimpleContainer([$this->getQuery()], []);
        $schema = new \Graphpinator\Typesystem\Schema($container, $this->getQuery(), $this->getMutation(), $this->getSubscription());
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

    public function getMutation() : \Graphpinator\Typesystem\Type
    {
        return new class extends \Graphpinator\Typesystem\Type {
            private int $order = 0;

            public function validateNonNullValue(mixed $rawValue) : bool
            {
                return true;
            }

            protected function getFieldDefinition() : \Graphpinator\Typesystem\Field\ResolvableFieldSet
            {
                return new \Graphpinator\Typesystem\Field\ResolvableFieldSet([
                    \Graphpinator\Typesystem\Field\ResolvableField::create(
                        'mutationField',
                        \Graphpinator\Typesystem\Container::Int()->notNull(),
                        function ($parent) : int {
                            $result = $this->order;
                            ++$this->order;

                            return $result;
                        },
                    ),
                ]);
            }
        };
    }

    public function getSubscription() : \Graphpinator\Typesystem\Type
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
                        'subscriptionField',
                        \Graphpinator\Typesystem\Container::Int()->notNull(),
                        static function ($parent) : int {
                            return 1;
                        },
                    ),
                ]);
            }
        };
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
                            return 321;
                        },
                    ),
                    \Graphpinator\Typesystem\Field\ResolvableField::create(
                        'field2',
                        OperationTest::getType2(),
                        static function () : int {
                            return 321;
                        },
                    ),
                ]);
            }
        };
    }
}
