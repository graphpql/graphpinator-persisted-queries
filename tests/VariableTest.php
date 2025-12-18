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
use Graphpinator\Typesystem\EnumType;
use Graphpinator\Typesystem\Field\ResolvableField;
use Graphpinator\Typesystem\Field\ResolvableFieldSet;
use Graphpinator\Typesystem\InputType;
use Graphpinator\Typesystem\Schema;
use Graphpinator\Typesystem\Type;
use Infinityloop\Utils\Json;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class VariableTest extends TestCase
{
    public static Type $queryType;
    public static Type $type;
    public static InputType $inputType;
    public static EnumType $enumType;

    public static function setUpBeforeClass() : void
    {
        self::$type = self::getType();
        self::$inputType = self::getSimpleInput();
        self::$enumType = self::getSimpleEnum();
        self::$queryType = self::getQuery();
    }

    public static function simpleDataProvider() : array
    {
        return [
            [
                Json::fromNative((object) [
                    'query' => 'query queryName ($var1: Int = 451) { field { fieldArg(arg1: $var1) } }',
                ]),
                2_440_439_348,
                '[{"type":"query","name":"queryName","selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":'
                . '"field","alias":"field","argumentValueSet":[],"directiveSet":[],'
                . '"selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":"fieldArg","alias":"fieldArg",'
                . '"argumentValueSet":[{"argument":"arg1","value":{"valueType":'
                . '"Graphpinator\\\Value\\\VariableValue","type":{"type":"named","name":"Int"},"variableName":"var1"}}],"directiveSet":[],'
                . '"selectionSet":null}]}],"variableSet":[{"name":"var1","type":{"type":"named","name":"Int"},'
                . '"defaultValue":{"valueType":"Graphpinator\\\Value\\\ScalarValue","type":{"type":"named","name":"Int"},"value":451}}],'
                . '"directiveSet":[]}]',
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
                    'query' => 'query queryName ($var1: Int = 451) { field { fieldArg(arg1: $var1) } }',
                ]),
                2_440_439_348,
                '[{"type":"query","name":"queryName","selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":'
                . '"field","alias":"field","argumentValueSet":[],"directiveSet":[],'
                . '"selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":"fieldArg","alias":"fieldArg",'
                . '"argumentValueSet":[{"argument":"arg1","value":{"valueType":'
                . '"Graphpinator\\\Value\\\VariableValue","type":{"type":"named","name":"Int"},"variableName":"var1"}}],"directiveSet":[],'
                . '"selectionSet":null}]}],"variableSet":[{"name":"var1","type":{"type":"named","name":"Int"},'
                . '"defaultValue":{"valueType":"Graphpinator\\\Value\\\ScalarValue","type":{"type":"named","name":"Int"},"value":451}}],'
                . '"directiveSet":[]}]',
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
                    'query' => 'query queryName ($var1: Int = null) { field { fieldArg1 } }',
                ]),
                1_527_189_668,
                '[{"type":"query","name":"queryName","selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":'
                . '"field","alias":"field","argumentValueSet":[],"directiveSet":[],'
                . '"selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":"fieldArg1","alias":"fieldArg1",'
                . '"argumentValueSet":[{"argument":"arg1","value":{"valueType":'
                . '"Graphpinator\\\Value\\\NullValue","type":{"type":"named","name":"Int"}}}],"directiveSet":[],"selectionSet":null'
                . '}]}],"variableSet":[{"name":"var1","type":{"type":"named","name":"Int"},"defaultValue":'
                . '{"valueType":"Graphpinator\\\Value\\\NullValue","type":{"type":"named","name":"Int"}}}],"directiveSet":[]}]',
                Json::fromNative((object) [
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
                505_400_837,
                '[{"type":"query","name":"queryName","selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":'
                . '"field","alias":"field","argumentValueSet":[],"directiveSet":[],'
                . '"selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":"fieldArg5","alias":"fieldArg5",'
                . '"argumentValueSet":[{"argument":"arg1","value":{"valueType":'
                . '"Graphpinator\\\Value\\\VariableValue","type":{"type":"notnull","inner":{"type":"named","name":"String"}},"variableName":'
                . '"var1"}}],"directiveSet":[],"selectionSet":null}]}],"variableSet":[{"name":"var1","type":{"type":'
                . '"notnull","inner":{"type":"named","name":"String"}},"defaultValue":{"valueType":"Graphpinator\\\Value\\\ScalarValue","type":'
                . '{"type":"named","name":"String"},"value":"opq"}}],"directiveSet":[]}]',
                Json::fromNative((object) [
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
                2_286_478_500,
                '[{"type":"query","name":"queryName","selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":'
                . '"field","alias":"field","argumentValueSet":[],"directiveSet":[],'
                . '"selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":"fieldArg5","alias":"fieldArg5",'
                . '"argumentValueSet":[{"argument":"arg1","value":{"valueType":'
                . '"Graphpinator\\\Value\\\ScalarValue","type":{"type":"named","name":"String"},"value":"abc"}}],"directiveSet":[],'
                . '"selectionSet":null}]}],"variableSet":[],"directiveSet":[]}]',
                Json::fromNative((object) [
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
                465_837_730,
                '[{"type":"query","name":"queryName","selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":'
                . '"field","alias":"field","argumentValueSet":[],"directiveSet":[]'
                . ',"selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":"fieldArg2","alias":"fieldArg2",'
                . '"argumentValueSet":[{"argument":"arg1","value":{"valueType":'
                . '"Graphpinator\\\Value\\\ListInputedValue","type":{"type":"list","inner":{"type":"named","name":"Int"}},"inner":[{"valueType"'
                . ':"Graphpinator\\\Value\\\ScalarValue","type":{"type":"named","name":"Int"},"value":100},{"valueType":'
                . '"Graphpinator\\\Value\\\ScalarValue","type":{"type":"named","name":"Int"},"value":200}]}}],"directiveSet":[],"selectionSet":null'
                . '}]}],"variableSet":[],"directiveSet":[]}]',
                Json::fromNative((object) [
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
                63_963_003,
                '[{"type":"query","name":"queryName","selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":'
                . '"field","alias":"field","argumentValueSet":[],"directiveSet":[]'
                . ',"selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":"fieldArg3","alias":"fieldArg3",'
                . '"argumentValueSet":[{"argument":"val","value":{"valueType":'
                . '"Graphpinator\\\Value\\\EnumValue","type":{"type":"named","name":"SimpleEnum"},"value":"B"}}],"directiveSet":[],"selectionSet"'
                . ':null}]}],"variableSet":[],"directiveSet":[]}]',
                Json::fromNative((object) [
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
                4_066_991_757,
                '[{"type":"query","name":"queryName","selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":'
                . '"field","alias":"field","argumentValueSet":[],"directiveSet":[],'
                . '"selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":"fieldArg4","alias":"fieldArg4",'
                . '"argumentValueSet":[{"argument":"val","value":{"valueType":'
                . '"Graphpinator\\\Value\\\InputValue","type":{"type":"named","name":"SimpleInput"},"inner":{"name":{"argument":"name",'
                . '"value":{"valueType":"Graphpinator\\\Value\\\ScalarValue","type":{"type":"named","name":"String"},"value":"B"}},"number":'
                . '{"argument":"number","value":{"valueType":"Graphpinator\\\Value\\\ScalarValue","type":{"type":"named","name":"Int"},"value"'
                . ':999}},"bool":{"argument":"bool","value":{"valueType":"Graphpinator\\\Value\\\ScalarValue","type":{"type":"named","name":'
                . '"Boolean"},"value":false}}}}}],"directiveSet":[],"selectionSet":null}]}],"variableSet":[],'
                . '"directiveSet":[]}]',
                Json::fromNative((object) [
                    'data' => [
                        'field' => [
                            'fieldArg4' => 999,
                        ],
                    ],
                ]),
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName ($var1: String = null, $var2: Int = 444, $var3: Boolean = false) { field { fieldArg6(arg1: $var1,'
                        . 'arg2: $var2, arg3: $var3) } }',
                ]),
                1_051_114_358,
                '[{"type":"query","name":"queryName","selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":'
                . '"field","alias":"field","argumentValueSet":[],"directiveSet":[],'
                . '"selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":"fieldArg6","alias":"fieldArg6",'
                . '"argumentValueSet":[{"argument":"arg1","value":{"valueType":'
                . '"Graphpinator\\\Value\\\VariableValue","type":{"type":"named","name":"String"},"variableName":"var1"}},{"argument":"arg2",'
                . '"value":{"valueType":"Graphpinator\\\Value\\\VariableValue","type":{"type":"named","name":"Int"},"variableName":"var2"}},'
                . '{"argument":"arg3","value":{"valueType":"Graphpinator\\\Value\\\VariableValue","type":{"type":"named","name":"Boolean"},'
                . '"variableName":"var3"}}],"directiveSet":[],"selectionSet":null}]}],"variableSet":[{"name":"var1",'
                . '"type":{"type":"named","name":"String"},"defaultValue":{"valueType":"Graphpinator\\\Value\\\NullValue","type":{"type":'
                . '"named","name":"String"}}},{"name":"var2","type":{"type":"named","name":"Int"},"defaultValue":{"valueType":'
                . '"Graphpinator\\\Value\\\ScalarValue","type":{"type":"named","name":"Int"},"value":444}},{"name":"var3","type":{"type":"named",'
                . '"name":"Boolean"},"defaultValue":{"valueType":"Graphpinator\\\Value\\\ScalarValue","type":{"type":"named","name":"Boolean"},'
                . '"value":false}}],"directiveSet":[]}]',
                Json::fromNative((object) [
                    'data' => [
                        'field' => [
                            'fieldArg6' => 'abc',
                        ],
                    ],
                ]),
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName ($var1: Int) { field { fieldArg1 } }',
                ]),
                3_302_446_931,
                '[{"type":"query","name":"queryName","selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":'
                . '"field","alias":"field","argumentValueSet":[],"directiveSet":[],'
                . '"selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":"fieldArg1","alias":"fieldArg1",'
                . '"argumentValueSet":[{"argument":"arg1","value":{"valueType":'
                . '"Graphpinator\\\Value\\\NullValue","type":{"type":"named","name":"Int"}}}],"directiveSet":[],"selectionSet":null'
                . '}]}],"variableSet":[{"name":"var1","type":{"type":"named","name":"Int"},"defaultValue":null}],'
                . '"directiveSet":[]}]',
                Json::fromNative((object) [
                    'data' => [
                        'field' => [
                            'fieldArg1' => 1,
                        ],
                    ],
                ]),
            ],
        ];
    }

    #[DataProvider('simpleDataProvider')]
    public function testSimple(Json $request, int $crc32, string $expectedCache, Json $expectedResult) : void
    {
        $container = new SimpleContainer([self::$queryType, self::$enumType, self::$inputType, self::$type], []);
        $schema = new Schema($container, self::$queryType);
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
        self::assertSame(
            $expectedResult->toString(),
            $result->toString(),
        );
    }

    #[DataProvider('simpleDataProvider')]
    public function testSimpleCache(Json $request, int $crc32, string $expectedCache, Json $expectedResult) : void
    {
        $container = new SimpleContainer([self::$queryType, self::$enumType, self::$inputType, self::$type], []);
        $schema = new Schema($container, self::$queryType);
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

    private static function getQuery() : Type
    {
        return new class () extends Type {
            protected const NAME = 'Query';

            public function validateNonNullValue(mixed $rawValue) : bool
            {
                return true;
            }

            protected function getFieldDefinition() : ResolvableFieldSet
            {
                return new ResolvableFieldSet([
                    ResolvableField::create(
                        'field',
                        VariableTest::$type->notNull(),
                        static function () : int {
                            return 1;
                        },
                    ),
                ]);
            }
        };
    }

    private static function getType() : Type
    {
        return new class extends Type {
            protected const NAME = 'Type';

            public function validateNonNullValue(mixed $rawValue) : bool
            {
                return true;
            }

            protected function getFieldDefinition() : ResolvableFieldSet
            {
                return new ResolvableFieldSet([
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
                    ResolvableField::create(
                        'fieldArg1',
                        Container::Int()->notNull(),
                        static function (int $parent, ?int $arg1 = null) : int {
                            return 1;
                        },
                    )->setArguments(new ArgumentSet([
                        Argument::create('arg1', Container::Int())
                            ->setDefaultValue(null),
                    ])),
                    ResolvableField::create(
                        'fieldArg2',
                        Container::Int()->notNull(),
                        static function (int $parent, ?array $arg1 = null) : int {
                            return $arg1[0];
                        },
                    )->setArguments(new ArgumentSet([
                        Argument::create('arg1', Container::Int()->list())
                            ->setDefaultValue(null),
                    ])),
                    ResolvableField::create(
                        'fieldArg3',
                        VariableTest::$enumType->notNull(),
                        static function ($parent, string $val) : string {
                            return $val;
                        },
                    )->setArguments(new ArgumentSet([
                        new Argument(
                            'val',
                            VariableTest::$enumType->notNull(),
                        ),
                    ])),
                    ResolvableField::create(
                        'fieldArg4',
                        Container::Int()->notNull(),
                        static function ($parent, \stdClass $val) : int {
                            return $val->number;
                        },
                    )->setArguments(new ArgumentSet([
                        new Argument(
                            'val',
                            VariableTest::$inputType->notNull(),
                        ),
                    ])),
                    ResolvableField::create(
                        'fieldArg5',
                        Container::String()->notNull(),
                        static function (int $parent, string $arg1) : string {
                            return $arg1;
                        },
                    )->setArguments(new ArgumentSet([
                        Argument::create('arg1', Container::String()->notNull())
                            ->setDefaultValue('abc'),
                    ])),
                    ResolvableField::create(
                        'fieldArg6',
                        Container::String()->notNull(),
                        static function (int $parent, ?string $arg1, ?int $arg2, ?bool $arg3) : string {
                            return 'abc';
                        },
                    )->setArguments(new ArgumentSet([
                        Argument::create('arg1', Container::String()),
                        Argument::create('arg2', Container::Int()),
                        Argument::create('arg3', Container::Boolean()),
                    ])),
                ]);
            }
        };
    }

    private static function getSimpleEnum() : EnumType
    {
        return new class extends EnumType
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

    private static function getSimpleInput() : InputType
    {
        return new class extends InputType
        {
            protected const NAME = 'SimpleInput';

            protected function getFieldDefinition() : ArgumentSet
            {
                return new ArgumentSet([
                    new Argument(
                        'name',
                        Container::String()->notNull(),
                    ),
                    new Argument(
                        'number',
                        Container::Int()->notNull(),
                    ),
                    new Argument(
                        'bool',
                        Container::Boolean(),
                    ),
                ]);
            }
        };
    }
}
