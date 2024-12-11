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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class OperationTest extends TestCase
{
    public static function getType2() : Type
    {
        return new class extends Type {
            public function validateNonNullValue(mixed $rawValue) : bool
            {
                return true;
            }

            protected function getFieldDefinition() : ResolvableFieldSet
            {
                return new ResolvableFieldSet([
                    ResolvableField::create(
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

    public static function getType() : Type
    {
        return new class extends Type {
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
                            return 123;
                        },
                    ),
                    ResolvableField::create(
                        'scalar',
                        Container::Int()->notNull(),
                        static function () : int {
                            return 987;
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

    public static function simpleDataProvider() : array
    {
        return [
            [
                Json::fromNative((object) [
                    'query' => 'query queryName { field { scalar } } query queryName2 { field { fieldArg } }',
                ]),
                1485165402,
                '[{"type":"query","name":"queryName","selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName"'
                . ':"field","alias":"field","argumentValueSet":[],"directiveSet":[],'
                . '"selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":"scalar","alias":"scalar",'
                . '"argumentValueSet":[],"directiveSet":[],"selectionSet":null}]'
                . '}],"variableSet":[],"directiveSet":[]},{"type":"query","name":"queryName2","selectionSet":[{"selectionType":'
                . '"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":"field",'
                . '"alias":"field","argumentValueSet":[],"directiveSet":[],"selectionSet":[{"selectionType":'
                . '"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":"fieldArg","alias":"fieldArg","argumentValueSet":'
                . '[{"argument":"arg1","value":{"valueType":"Graphpinator\\\Value\\\ScalarValue","type":{"type":"named","name":"Int"},"value":123}}],'
                . '"directiveSet":[],"selectionSet":null}]}],"variableSet":[],"directiveSet":[]}]',
                Json::fromNative((object) ['data' => ['field' => ['scalar' => 987]]]),
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName { field { scalar } } query queryName2 { field2 { field { fieldArg } } }',
                ]),
                1589897943,
                '[{"type":"query","name":"queryName","selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field",'
                . '"fieldName":"field","alias":"field","argumentValueSet":[],"directiveSet":[],'
                . '"selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":"scalar","alias":'
                . '"scalar","argumentValueSet":[],"directiveSet":[],"selectionSet":null}]'
                . '}],"variableSet":[],"directiveSet":[]},{"type":"query","name":"queryName2","selectionSet":[{"selectionType":'
                . '"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":"field2",'
                . '"alias":"field2","argumentValueSet":[],"directiveSet":[],"selectionSet":[{"selectionType":'
                . '"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":"field","alias":"field","argumentValueSet":[]'
                . ',"directiveSet":[],"selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":"fieldArg",'
                . '"alias":"fieldArg","argumentValueSet":[{"argument":"arg1","value":'
                . '{"valueType":"Graphpinator\\\Value\\\ScalarValue","type":{"type":"named","name":"Int"},"value":123}}],"directiveSet":[],'
                . '"selectionSet":null}]}]}],"variableSet":[],"directiveSet":[]}]',
                Json::fromNative((object) ['data' => ['field' => ['scalar' => 987]]]),
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName { field { scalar } } mutation mutationName { mutationField, secondField: mutationField, '
                        . 'thirdField: mutationField }',
                ]),
                2364436310,
                '[{"type":"query","name":"queryName","selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field",'
                . '"fieldName":"field","alias":"field","argumentValueSet":[],"directiveSet":[],'
                . '"selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":"scalar","alias":"scalar",'
                . '"argumentValueSet":[],"directiveSet":[],"selectionSet":null}]'
                . '}],"variableSet":[],"directiveSet":[]},{"type":"mutation","name":"mutationName","selectionSet":[{"selectionType":'
                . '"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":'
                . '"mutationField","alias":"mutationField","argumentValueSet":[],"directiveSet":[],"selectionSet":null},{"selectionType":'
                . '"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName"'
                . ':"mutationField","alias":"secondField","argumentValueSet":[],"directiveSet":[],"selectionSet":null},{"selectionType":'
                . '"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":'
                . '"mutationField","alias":"thirdField","argumentValueSet":[],"directiveSet":[],"selectionSet":null}],"variableSet":[]'
                . ',"directiveSet":[]}]',
                Json::fromNative((object) ['data' => ['field' => ['scalar' => 987]]]),
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName { field { scalar } } mutation mutationName { mutationField, secondField:'
                        . 'mutationField, thirdField: mutationField } subscription subscriptionName { subscriptionField }',
                ]),
                1309780448,
                '[{"type":"query","name":"queryName","selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":'
                . '"field","alias":"field","argumentValueSet":[],"directiveSet":[],'
                . '"selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":"scalar","alias":"scalar",'
                . '"argumentValueSet":[],"directiveSet":[],"selectionSet":null}]'
                . '}],"variableSet":[],"directiveSet":[]},{"type":"mutation","name":"mutationName","selectionSet":[{"selectionType":'
                . '"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":'
                . '"mutationField","alias":"mutationField","argumentValueSet":[],"directiveSet":[],"selectionSet":null},{"selectionType":'
                . '"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName"'
                . ':"mutationField","alias":"secondField","argumentValueSet":[],"directiveSet":[],"selectionSet":null},{"selectionType":'
                . '"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":'
                . '"mutationField","alias":"thirdField","argumentValueSet":[],"directiveSet":[],"selectionSet":null}],"variableSet":[]'
                . ',"directiveSet":[]},{"type":"subscription","name":"subscriptionName","selectionSet":[{"selectionType":'
                . '"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":"subscriptionField","alias":'
                . '"subscriptionField","argumentValueSet":[],"directiveSet":[],"selectionSet":null}],"variableSet":[],'
                . '"directiveSet":[]}]',
                Json::fromNative((object) ['data' => ['field' => ['scalar' => 987]]]),
            ],
        ];
    }

    #[DataProvider('simpleDataProvider')]
    public function testSimple(Json $request, int $crc32, string $expectedCache, Json $expectedResult) : void
    {
        $container = new SimpleContainer([$this->getQuery()], []);
        $schema = new Schema($container, $this->getQuery(), $this->getMutation(), $this->getSubscription());
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
        $container = new SimpleContainer([$this->getQuery()], []);
        $schema = new Schema($container, $this->getQuery(), $this->getMutation(), $this->getSubscription());
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

    public function getMutation() : Type
    {
        return new class extends Type {
            private int $order = 0;

            public function validateNonNullValue(mixed $rawValue) : bool
            {
                return true;
            }

            protected function getFieldDefinition() : ResolvableFieldSet
            {
                return new ResolvableFieldSet([
                    ResolvableField::create(
                        'mutationField',
                        Container::Int()->notNull(),
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

    public function getSubscription() : Type
    {
        return new class extends Type {
            public function validateNonNullValue(mixed $rawValue) : bool
            {
                return true;
            }

            protected function getFieldDefinition() : ResolvableFieldSet
            {
                return new ResolvableFieldSet([
                    ResolvableField::create(
                        'subscriptionField',
                        Container::Int()->notNull(),
                        static function ($parent) : int {
                            return 1;
                        },
                    ),
                ]);
            }
        };
    }

    private function getQuery() : Type
    {
        return new class ($this->getType()) extends Type {
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
                            return 321;
                        },
                    ),
                    ResolvableField::create(
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
