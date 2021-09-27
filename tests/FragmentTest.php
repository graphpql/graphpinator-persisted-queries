<?php

declare(strict_types = 1);

namespace Graphpinator\PersistedQueries\Tests;

use \Infinityloop\Utils\Json;

final class FragmentTest extends \PHPUnit\Framework\TestCase
{
    public static function getQuery() : \Graphpinator\Typesystem\Type
    {
        return new class extends \Graphpinator\Typesystem\Type {
            protected const NAME = 'Query';

            public function validateNonNullValue(mixed $rawValue) : bool
            {
                return true;
            }

            protected function getFieldDefinition() : \Graphpinator\Typesystem\Field\ResolvableFieldSet
            {
                return new \Graphpinator\Typesystem\Field\ResolvableFieldSet([
                    \Graphpinator\Typesystem\Field\ResolvableField::create(
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

    public static function getType() : \Graphpinator\Typesystem\Type
    {
        return new class extends \Graphpinator\Typesystem\Type {
            protected const NAME = 'Type';

            public function validateNonNullValue(mixed $rawValue) : bool
            {
                return true;
            }

            protected function getFieldDefinition() : \Graphpinator\Typesystem\Field\ResolvableFieldSet
            {
                return new \Graphpinator\Typesystem\Field\ResolvableFieldSet([
                    \Graphpinator\Typesystem\Field\ResolvableField::create(
                        'scalar',
                        \Graphpinator\Typesystem\Container::Int()->notNull(),
                        static function () : int {
                            return 1;
                        },
                    ),
                    \Graphpinator\Typesystem\Field\ResolvableField::create(
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

    public static function getFlexSimple() : \Graphpinator\Typesystem\InterfaceType
    {
        return new class extends \Graphpinator\Typesystem\InterfaceType {
            protected const NAME = 'FlexSimple';

            public function createResolvedValue($rawValue) : \Graphpinator\Value\TypeIntermediateValue
            {
                return new \Graphpinator\Value\TypeIntermediateValue(
                    FragmentTest::getFlexType(),
                    123,
                );
            }

            protected function getFieldDefinition() : \Graphpinator\Typesystem\Field\FieldSet
            {
                return new \Graphpinator\Typesystem\Field\FieldSet([
                    new \Graphpinator\Typesystem\Field\Field('mandatory', \Graphpinator\Typesystem\Container::Int()),
                ]);
            }
        };
    }

    public static function getFlexType() : \Graphpinator\Typesystem\Type
    {
        return new class extends \Graphpinator\Typesystem\Type
        {
            protected const NAME = 'FlexType';

            public function __construct()
            {
                parent::__construct(new \Graphpinator\Typesystem\InterfaceSet([FragmentTest::getFlexSimple()]));
            }

            public function validateNonNullValue($rawValue) : bool
            {
                return true;
            }

            protected function getFieldDefinition() : \Graphpinator\Typesystem\Field\ResolvableFieldSet
            {
                return new \Graphpinator\Typesystem\Field\ResolvableFieldSet([
                    \Graphpinator\Typesystem\Field\ResolvableField::create(
                        'mandatory',
                        \Graphpinator\Typesystem\Container::Int(),
                        static function () : int {
                            return 123;
                        },
                    ),
                    \Graphpinator\Typesystem\Field\ResolvableField::create(
                        'specific',
                        \Graphpinator\Typesystem\Container::String(),
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

        $container = new \Graphpinator\SimpleContainer([self::getQuery(), self::getType(), self::getFlexSimple(), self::getFlexType()], []);
        $schema = new \Graphpinator\Typesystem\Schema($container, self::getQuery());
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

        self::assertSame(
            Json::fromNative(['data' => ['field' => ['scalar' => 1, 'flex' => ['mandatory' => 123, 'specific' => 'flexTypeResult']]]])->toString(),
            $graphpinator->run(new \Graphpinator\Request\JsonRequestFactory($request))->toString(),
        );

        self::assertSame(
            Json::fromNative(['data' => ['field' => ['scalar' => 1, 'flex' => ['mandatory' => 123, 'specific' => 'flexTypeResult']]]])->toString(),
            $graphpinator->run(new \Graphpinator\Request\JsonRequestFactory($request))->toString(),
        );

        $this->assertArrayHasKey(3380607393, $cache);
    }
}
