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
                        'typeField',
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
                    \Graphpinator\Typesystem\Field\ResolvableField::create(
                        'simpleListing',
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
                    \Graphpinator\Container\Container::Int(),
                    123,
                );
            }

            protected function getFieldDefinition() : \Graphpinator\Typesystem\Field\FieldSet
            {
                return new \Graphpinator\Typesystem\Field\FieldSet([
                    \Graphpinator\Typesystem\Field\Field::create(
                        'flex',
                        FragmentTest::getFlexType(),
                    ),
                ]);
            }
        };
    }

    public static function getFlexType() : \Graphpinator\Typesystem\Type
    {
        return new class extends \Graphpinator\Typesystem\Type
        {
            protected const NAME = 'FlexType';

            public function validateNonNullValue($rawValue) : bool
            {
                return true;
            }

            protected function getFieldDefinition() : \Graphpinator\Typesystem\Field\ResolvableFieldSet
            {
                return new \Graphpinator\Typesystem\Field\ResolvableFieldSet([
                    \Graphpinator\Typesystem\Field\ResolvableField::create(
                        'type',
                        \Graphpinator\Container\Container::String(),
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
                    ... on FlexSimple {
                        flex { type }
                    }
                }
            }',
        ]);

        $container = new \Graphpinator\SimpleContainer([self::getQuery(), self::getType(), self::getFlexSimple()], []);
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

        $graphpinator->run(new \Graphpinator\Request\JsonRequestFactory($request));

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

        $graphpinator->run(new \Graphpinator\Request\JsonRequestFactory($request));

        $this->assertArrayHasKey(1366466509, $cache);
    }
}
