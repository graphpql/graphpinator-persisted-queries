<?php

declare(strict_types = 1);

namespace Graphpinator\PersistedQueries\Tests;

use \Infinityloop\Utils\Json;

final class TypeTest extends \PHPUnit\Framework\TestCase
{
    public static function getInterface() : \Graphpinator\Typesystem\InterfaceType
    {
        return new class extends \Graphpinator\Typesystem\InterfaceType
        {
            protected const NAME = 'InterfaceAbc';
            protected const DESCRIPTION = 'Interface Abc Description';

            public function createResolvedValue($rawValue) : \Graphpinator\Value\TypeIntermediateValue
            {
                return new \Graphpinator\Value\TypeIntermediateValue(TypeTest::getType(), $rawValue);
            }

            protected function getFieldDefinition() : \Graphpinator\Typesystem\Field\FieldSet
            {
                return new \Graphpinator\Typesystem\Field\FieldSet([
                    new \Graphpinator\Typesystem\Field\Field(
                        'name',
                        \Graphpinator\Typesystem\Container::String()->notNull(),
                    ),
                ]);
            }
        };
    }

    public static function getType() : \Graphpinator\Typesystem\Type
    {
        return new class extends \Graphpinator\Typesystem\Type
        {
            protected const NAME = 'type';
            protected const DESCRIPTION = null;

            public function __construct()
            {
                parent::__construct(new \Graphpinator\Typesystem\InterfaceSet([
                    TypeTest::getInterface(),
                ]));
            }

            public function validateNonNullValue($rawValue) : bool
            {
                return true;
            }

            protected function getFieldDefinition() : \Graphpinator\Typesystem\Field\ResolvableFieldSet
            {
                return new \Graphpinator\Typesystem\Field\ResolvableFieldSet([
                    \Graphpinator\Typesystem\Field\ResolvableField::create(
                        'name',
                        \Graphpinator\Typesystem\Container::String()->notNull(),
                        static function (\stdClass $parent, string $name = 'defaultA') {
                            return $parent->name
                                ?? $name;
                        },
                    )->setArguments(new \Graphpinator\Typesystem\Argument\ArgumentSet([
                        \Graphpinator\Typesystem\Argument\Argument::create(
                            'name',
                            \Graphpinator\Typesystem\Container::String()->notNull(),
                        )->setDefaultValue('defaultA'),
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
                    'query' => 'query queryName { field { name } }',
                ]),
                659090205,
                '[{"type":"query","name":"queryName","fieldSet":[{"fieldName":"field","alias":"field","argumentValueSet":[],"directiveSet":[],'
                . '"fieldSet":[{"fieldName":"name","alias":"name","argumentValueSet":[{"argument":"name","value":{"valueType":'
                . '"Graphpinator\\\Value\\\ScalarValue","type":{"type":"named","name":"String"},"value":"defaultA"}}],"directiveSet":[],'
                . '"fieldSet":null,"typeCond":null}],"typeCond":null}],"variableSet":[],"directiveSet":[]}]',
                \Infinityloop\Utils\Json::fromNative((object) ['data' => ['field' => ['name' => 'defaultA']]]),
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
        $container = new \Graphpinator\SimpleContainer([$this->getQuery()], []);
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
                        static function () : \stdClass {
                            return (object) [
                                'name' => null,
                            ];
                        },
                    ),
                ]);
            }
        };
    }
}
