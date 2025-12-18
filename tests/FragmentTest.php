<?php

declare(strict_types = 1);

namespace Graphpinator\PersistedQueries\Tests;

use Graphpinator\Graphpinator;
use Graphpinator\Module\ModuleSet;
use Graphpinator\PersistedQueries\PersistedQueriesModule;
use Graphpinator\Request\JsonRequestFactory;
use Graphpinator\SimpleContainer;
use Graphpinator\Typesystem\Container;
use Graphpinator\Typesystem\Field\Field;
use Graphpinator\Typesystem\Field\FieldSet;
use Graphpinator\Typesystem\Field\ResolvableField;
use Graphpinator\Typesystem\Field\ResolvableFieldSet;
use Graphpinator\Typesystem\InterfaceSet;
use Graphpinator\Typesystem\InterfaceType;
use Graphpinator\Typesystem\Schema;
use Graphpinator\Typesystem\Type;
use Graphpinator\Value\TypeIntermediateValue;
use Infinityloop\Utils\Json;
use PHPUnit\Framework\TestCase;

final class FragmentTest extends TestCase
{
    private static Type $queryType;
    private static Type $type;
    private static Type $flexType;
    private static InterfaceType $flexSimple;

    public static function setUpBeforeClass() : void
    {
        self::$flexSimple = self::getFlexSimple();
        self::$flexType = self::getFlexType();
        self::$type = self::getType();
        self::$queryType = self::getQuery();
    }

    public static function getQuery() : Type
    {
        return new class extends Type {
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
                        FragmentTest::getType(),
                        static function () : int {
                            return 1;
                        },
                    ),
                ]);
            }
        };
    }

    public static function getType() : Type
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
                        'scalar',
                        Container::Int()->notNull(),
                        static function () : int {
                            return 1;
                        },
                    ),
                    ResolvableField::create(
                        'flex',
                        FragmentTest::getFlexSimple(),
                        static function () : int {
                            return 1;
                        },
                    ),
                ]);
            }
        };
    }

    public static function getFlexSimple() : InterfaceType
    {
        return new class extends InterfaceType {
            protected const NAME = 'FlexSimple';

            public function createResolvedValue($rawValue) : TypeIntermediateValue
            {
                return new TypeIntermediateValue(
                    FragmentTest::getFlexType(),
                    123,
                );
            }

            protected function getFieldDefinition() : FieldSet
            {
                return new FieldSet([
                    new Field('mandatory', Container::Int()),
                ]);
            }
        };
    }

    public static function getFlexType() : Type
    {
        return new class extends Type
        {
            protected const NAME = 'FlexType';

            public function __construct()
            {
                parent::__construct(new InterfaceSet([FragmentTest::getFlexSimple()]));
            }

            public function validateNonNullValue($rawValue) : bool
            {
                return true;
            }

            protected function getFieldDefinition() : ResolvableFieldSet
            {
                return new ResolvableFieldSet([
                    ResolvableField::create(
                        'mandatory',
                        Container::Int(),
                        static function () : int {
                            return 123;
                        },
                    ),
                    ResolvableField::create(
                        'specific',
                        Container::String(),
                        static function () : string {
                            return 'flexTypeResult';
                        },
                    ),
                ]);
            }
        };
    }

    public function testSimple() : void
    {
        $request = Json::fromNative((object) [
            'query' => 'query {
                field {
                  scalar
                  flex {
                    mandatory
                    ... on FlexType { specific }
                  }
                }
            }',
        ]);

        $container = new SimpleContainer([self::$queryType, self::$type, self::$flexSimple, self::$flexType], []);
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

        self::assertSame(
            Json::fromNative(['data' => ['field' => ['scalar' => 1, 'flex' => ['mandatory' => 123, 'specific' => 'flexTypeResult']]]])->toString(),
            $graphpinator->run(new JsonRequestFactory($request))->toString(),
        );

        self::assertSame(
            Json::fromNative(['data' => ['field' => ['scalar' => 1, 'flex' => ['mandatory' => 123, 'specific' => 'flexTypeResult']]]])->toString(),
            $graphpinator->run(new JsonRequestFactory($request))->toString(),
        );

        $this->assertArrayHasKey(3_380_607_393, $cache);
    }
}
