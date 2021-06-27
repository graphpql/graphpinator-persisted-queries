<?php

declare(strict_types = 1);

namespace Graphpinator\PersistedQueries\Tests;

use \Infinityloop\Utils\Json;

final class VariableTest extends \PHPUnit\Framework\TestCase
{
    public function simpleDataProvider() : array
    {
        return [
            [
                Json::fromNative((object) [
                    'query' => 'query queryName ($var1: Int = 451) { field { fieldArg(arg1: $var1) } }',
                ]),
                2440439348,
                '[{"type":"query","name":"queryName","fieldSet":[{"fieldName":"field","alias":"field","argumentValueSet":[],"directiveSet":[],'
                . '"fieldSet":[{"fieldName":"fieldArg","alias":"fieldArg","argumentValueSet":[{"argument":"arg1","value":{"valueType":'
                . '"Graphpinator\\\Value\\\VariableValue","type":{"type":"named","name":"Int"},"variableName":"var1"}}],"directiveSet":[],'
                . '"fieldSet":null,"typeCond":null}],"typeCond":null}],"variableSet":[{"name":"var1","type":{"type":"named","name":"Int"},'
                . '"defaultValue":{"valueType":"Graphpinator\\\Value\\\ScalarValue","type":{"type":"named","name":"Int"},"value":451}}],"directiveSet":[]}]',
                \Infinityloop\Utils\Json::fromNative((object) [
                    'data' => [
                        'field' => [
                            'fieldArg' => 1,
                        ]
                    ]
                ]),
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName ($var1: Int = 451) { field { fieldArg(arg1: $var1) } }',
                ]),
                2440439348,
                '[{"type":"query","name":"queryName","fieldSet":[{"fieldName":"field","alias":"field","argumentValueSet":[],"directiveSet":[],'
                . '"fieldSet":[{"fieldName":"fieldArg","alias":"fieldArg","argumentValueSet":[{"argument":"arg1","value":{"valueType":'
                . '"Graphpinator\\\Value\\\VariableValue","type":{"type":"named","name":"Int"},"variableName":"var1"}}],"directiveSet":[],'
                . '"fieldSet":null,"typeCond":null}],"typeCond":null}],"variableSet":[{"name":"var1","type":{"type":"named","name":"Int"},'
                . '"defaultValue":{"valueType":"Graphpinator\\\Value\\\ScalarValue","type":{"type":"named","name":"Int"},"value":451}}],"directiveSet":[]}]',
                \Infinityloop\Utils\Json::fromNative((object) [
                    'data' => [
                        'field' => [
                            'fieldArg' => 1,
                        ]
                    ]
                ]),
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName ($var1: Int = null) { field { fieldArg1 } }',
                ]),
                1527189668,
                '[{"type":"query","name":"queryName","fieldSet":[{"fieldName":"field","alias":"field","argumentValueSet":[],"directiveSet":[],"fieldSet":[{"fieldName":"fieldArg1","alias":"fieldArg1","argumentValueSet":[{"argument":"arg1","value":{"valueType":"Graphpinator\\\Value\\\NullInputedValue","type":{"type":"named","name":"Int"}}}],"directiveSet":[],"fieldSet":null,"typeCond":null}],"typeCond":null}],"variableSet":[{"name":"var1","type":{"type":"named","name":"Int"},"defaultValue":{"valueType":"Graphpinator\\\Value\\\NullInputedValue","type":{"type":"named","name":"Int"}}}],"directiveSet":[]}]',
                \Infinityloop\Utils\Json::fromNative((object) [
                    'data' => [
                        'field' => [
                            'fieldArg1' => 1,
                        ],
                    ],
                ]),
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName ($var1: String! = "opq") { field { fieldArg5(arg1: $var1) } }',
                ]),
                505400837,
                '[{"type":"query","name":"queryName","fieldSet":[{"fieldName":"field","alias":"field","argumentValueSet":[],"directiveSet":[],"fieldSet":[{"fieldName":"fieldArg5","alias":"fieldArg5","argumentValueSet":[{"argument":"arg1","value":{"valueType":"Graphpinator\\\Value\\\VariableValue","type":{"type":"notnull","inner":{"type":"named","name":"String"}},"variableName":"var1"}}],"directiveSet":[],"fieldSet":null,"typeCond":null}],"typeCond":null}],"variableSet":[{"name":"var1","type":{"type":"notnull","inner":{"type":"named","name":"String"}},"defaultValue":{"valueType":"Graphpinator\\\Value\\\ScalarValue","type":{"type":"named","name":"String"},"value":"opq"}}],"directiveSet":[]}]',
                \Infinityloop\Utils\Json::fromNative((object) [
                    'data' => [
                        'field' => [
                            'fieldArg5' => 'opq',
                        ],
                    ],
                ]),
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName { field { fieldArg5 } }',
                ]),
                2286478500,
                '[{"type":"query","name":"queryName","fieldSet":[{"fieldName":"field","alias":"field","argumentValueSet":[],"directiveSet":[],"fieldSet":[{"fieldName":"fieldArg5","alias":"fieldArg5","argumentValueSet":[{"argument":"arg1","value":{"valueType":"Graphpinator\\\Value\\\ScalarValue","type":{"type":"named","name":"String"},"value":"abc"}}],"directiveSet":[],"fieldSet":null,"typeCond":null}],"typeCond":null}],"variableSet":[],"directiveSet":[]}]',
                \Infinityloop\Utils\Json::fromNative((object) [
                    'data' => [
                        'field' => [
                            'fieldArg5' => 'abc',
                        ],
                    ],
                ]),
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName { field { fieldArg2(arg1: [100, 200]) } }',
                ]),
                465837730,
                '[{"type":"query","name":"queryName","fieldSet":[{"fieldName":"field","alias":"field","argumentValueSet":[],"directiveSet":[],"fieldSet":[{"fieldName":"fieldArg2","alias":"fieldArg2","argumentValueSet":[{"argument":"arg1","value":{"valueType":"Graphpinator\\\Value\\\ListInputedValue","type":{"type":"list","inner":{"type":"named","name":"Int"}},"inner":[{"valueType":"Graphpinator\\\Value\\\ScalarValue","type":{"type":"named","name":"Int"},"value":100},{"valueType":"Graphpinator\\\Value\\\ScalarValue","type":{"type":"named","name":"Int"},"value":200}]}}],"directiveSet":[],"fieldSet":null,"typeCond":null}],"typeCond":null}],"variableSet":[],"directiveSet":[]}]',
                \Infinityloop\Utils\Json::fromNative((object) [
                    'data' => [
                        'field' => [
                            'fieldArg2' => 100,
                        ],
                    ],
                ]),
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName { field { fieldArg3(val: "B") } }',
                ]),
                63963003,
                '[{"type":"query","name":"queryName","fieldSet":[{"fieldName":"field","alias":"field","argumentValueSet":[],"directiveSet":[],"fieldSet":[{"fieldName":"fieldArg3","alias":"fieldArg3","argumentValueSet":[{"argument":"val","value":{"valueType":"Graphpinator\\\Value\\\EnumValue","type":{"type":"named","name":"SimpleEnum"},"value":"B"}}],"directiveSet":[],"fieldSet":null,"typeCond":null}],"typeCond":null}],"variableSet":[],"directiveSet":[]}]',
                \Infinityloop\Utils\Json::fromNative((object) [
                    'data' => [
                        'field' => [
                            'fieldArg3' => 'B',
                        ],
                    ],
                ]),
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName { field { fieldArg4(val: { name: "B", number: 999, bool: false }) } }',
                ]),
                4066991757,
                '[{"type":"query","name":"queryName","fieldSet":[{"fieldName":"field","alias":"field","argumentValueSet":[],"directiveSet":[],"fieldSet":[{"fieldName":"fieldArg4","alias":"fieldArg4","argumentValueSet":[{"argument":"val","value":{"valueType":"Graphpinator\\\Value\\\InputValue","type":{"type":"named","name":"SimpleInput"},"inner":{"name":{"argument":"name","value":{"valueType":"Graphpinator\\\Value\\\ScalarValue","type":{"type":"named","name":"String"},"value":"B"}},"number":{"argument":"number","value":{"valueType":"Graphpinator\\\Value\\\ScalarValue","type":{"type":"named","name":"Int"},"value":999}},"bool":{"argument":"bool","value":{"valueType":"Graphpinator\\\Value\\\ScalarValue","type":{"type":"named","name":"Boolean"},"value":false}}}}}],"directiveSet":[],"fieldSet":null,"typeCond":null}],"typeCond":null}],"variableSet":[],"directiveSet":[]}]',
                \Infinityloop\Utils\Json::fromNative((object) [
                    'data' => [
                        'field' => [
                            'fieldArg4' => 999,
                        ],
                    ],
                ]),
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
        $container = new \Graphpinator\SimpleContainer([
            $this->getQuery(),
            'SimpleEnum' => self::getSimpleEnum(),
            'SimpleInput' => self::getSimpleInput(),
        ], []);
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

        $result = $graphpinator->run(new \Graphpinator\Request\JsonRequestFactory($request));

        $this->assertArrayHasKey($crc32, $cache);
        $this->assertEquals($expectedCache, $cache[$crc32]);
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
        $container = new \Graphpinator\SimpleContainer([
            $this->getQuery(),
            'SimpleEnum' => self::getSimpleEnum(),
            'SimpleInput' => self::getSimpleInput(),
        ], []);
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
            protected const NAME = 'type';

            public function validateNonNullValue(mixed $rawValue) : bool
            {
                return true;
            }

            protected function getFieldDefinition() : \Graphpinator\Typesystem\Field\ResolvableFieldSet
            {
                return new \Graphpinator\Typesystem\Field\ResolvableFieldSet([
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
                    \Graphpinator\Typesystem\Field\ResolvableField::create(
                        'fieldArg1',
                        \Graphpinator\Container\Container::Int()->notNull(),
                        static function (int $parent, ?int $arg1 = null) : int {
                            return 1;
                        },
                    )->setArguments(new \Graphpinator\Typesystem\Argument\ArgumentSet([
                        \Graphpinator\Typesystem\Argument\Argument::create('arg1', \Graphpinator\Typesystem\Container::Int())
                            ->setDefaultValue(null),
                    ])),
                    \Graphpinator\Typesystem\Field\ResolvableField::create(
                        'fieldArg2',
                        \Graphpinator\Container\Container::Int()->notNull(),
                        static function (int $parent, ?array $arg1 = null) : int {
                            return $arg1[0];
                        },
                    )->setArguments(new \Graphpinator\Typesystem\Argument\ArgumentSet([
                        \Graphpinator\Typesystem\Argument\Argument::create('arg1', \Graphpinator\Typesystem\Container::Int()->list())
                            ->setDefaultValue(null),
                    ])),
                    \Graphpinator\Typesystem\Field\ResolvableField::create(
                        'fieldArg3',
                        VariableTest::getSimpleEnum()->notNull(),
                        static function ($parent, string $val) : string {
                            return $val;
                        },
                    )->setArguments(new \Graphpinator\Typesystem\Argument\ArgumentSet([
                        new \Graphpinator\Typesystem\Argument\Argument(
                            'val',
                            VariableTest::getSimpleEnum()->notNull(),
                        ),
                    ])),
                    \Graphpinator\Typesystem\Field\ResolvableField::create(
                        'fieldArg4',
                        \Graphpinator\Container\Container::Int()->notNull(),
                        static function ($parent, \stdClass $val) : int {
                            return $val->number;
                        },
                    )->setArguments(new \Graphpinator\Typesystem\Argument\ArgumentSet([
                        new \Graphpinator\Typesystem\Argument\Argument(
                            'val',
                            VariableTest::getSimpleInput()->notNull(),
                        ),
                    ])),
                    \Graphpinator\Typesystem\Field\ResolvableField::create(
                        'fieldArg5',
                        \Graphpinator\Container\Container::String()->notNull(),
                        static function (int $parent, string $arg1) : string {
                            return $arg1;
                        },
                    )->setArguments(new \Graphpinator\Typesystem\Argument\ArgumentSet([
                        \Graphpinator\Typesystem\Argument\Argument::create('arg1', \Graphpinator\Typesystem\Container::String()->notNull())
                            ->setDefaultValue('abc'),
                    ])),
                ]);
            }
        };
    }

    public static function getSimpleEnum() : \Graphpinator\Typesystem\EnumType
    {
        return new class extends \Graphpinator\Typesystem\EnumType
        {
            public const A = 'A';
            public const B = 'B';
            public const C = 'C';
            public const D = 'D';

            protected const NAME = 'SimpleEnum';

            public function __construct()
            {
                parent::__construct(self::fromConstants());
            }
        };
    }

    public static function getSimpleInput() : \Graphpinator\Typesystem\InputType
    {
        return new class extends \Graphpinator\Typesystem\InputType
        {
            protected const NAME = 'SimpleInput';

            protected function getFieldDefinition() : \Graphpinator\Typesystem\Argument\ArgumentSet
            {
                return new \Graphpinator\Typesystem\Argument\ArgumentSet([
                    new \Graphpinator\Typesystem\Argument\Argument(
                        'name',
                        \Graphpinator\Typesystem\Container::String()->notNull(),
                    ),
                    new \Graphpinator\Typesystem\Argument\Argument(
                        'number',
                        \Graphpinator\Typesystem\Container::Int()->notNull(),
                    ),
                    new \Graphpinator\Typesystem\Argument\Argument(
                        'bool',
                        \Graphpinator\Typesystem\Container::Boolean(),
                    ),
                ]);
            }
        };
    }
}
