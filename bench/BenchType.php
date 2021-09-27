<?php

declare(strict_types = 1);

namespace Graphpinator\PersistedQueries\Bench;

class BenchType extends \Graphpinator\Typesystem\Type
{
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
                \Graphpinator\Typesystem\Container::Int()->notNull(),
                static function (int $parent, int $arg1) : int {
                    return 1;
                },
            )->setArguments(new \Graphpinator\Typesystem\Argument\ArgumentSet([
                \Graphpinator\Typesystem\Argument\Argument::create('arg1', \Graphpinator\Typesystem\Container::Int())
                    ->setDefaultValue(123),
            ])),
            \Graphpinator\Typesystem\Field\ResolvableField::create(
                'fieldArg1',
                \Graphpinator\Typesystem\Container::Int()->notNull(),
                static function (int $parent, ?int $arg1 = null) : int {
                    return 1;
                },
            )->setArguments(new \Graphpinator\Typesystem\Argument\ArgumentSet([
                \Graphpinator\Typesystem\Argument\Argument::create('arg1', \Graphpinator\Typesystem\Container::Int())
                    ->setDefaultValue(null),
            ])),
            \Graphpinator\Typesystem\Field\ResolvableField::create(
                'fieldArg2',
                \Graphpinator\Typesystem\Container::Int()->notNull(),
                static function (int $parent, ?array $arg1 = null) : int {
                    return $arg1[0];
                },
            )->setArguments(new \Graphpinator\Typesystem\Argument\ArgumentSet([
                \Graphpinator\Typesystem\Argument\Argument::create('arg1', \Graphpinator\Typesystem\Container::Int()->list())
                    ->setDefaultValue(null),
            ])),
            \Graphpinator\Typesystem\Field\ResolvableField::create(
                'fieldArg3',
                \Graphpinator\PersistedQueries\Tests\VariableTest::getSimpleEnum()->notNull(),
                static function ($parent, string $val) : string {
                    return $val;
                },
            )->setArguments(new \Graphpinator\Typesystem\Argument\ArgumentSet([
                new \Graphpinator\Typesystem\Argument\Argument(
                    'val',
                    \Graphpinator\PersistedQueries\Tests\VariableTest::getSimpleEnum()->notNull(),
                ),
            ])),
            \Graphpinator\Typesystem\Field\ResolvableField::create(
                'fieldArg4',
                \Graphpinator\Typesystem\Container::Int()->notNull(),
                static function ($parent, \stdClass $val) : int {
                    return $val->number;
                },
            )->setArguments(new \Graphpinator\Typesystem\Argument\ArgumentSet([
                new \Graphpinator\Typesystem\Argument\Argument(
                    'val',
                    \Graphpinator\PersistedQueries\Tests\VariableTest::getSimpleInput()->notNull(),
                ),
            ])),
            \Graphpinator\Typesystem\Field\ResolvableField::create(
                'fieldArg5',
                \Graphpinator\Typesystem\Container::String()->notNull(),
                static function (int $parent, string $arg1) : string {
                    return $arg1;
                },
            )->setArguments(new \Graphpinator\Typesystem\Argument\ArgumentSet([
                \Graphpinator\Typesystem\Argument\Argument::create('arg1', \Graphpinator\Typesystem\Container::String()->notNull())
                    ->setDefaultValue('abc'),
            ])),
            \Graphpinator\Typesystem\Field\ResolvableField::create(
                'fieldArg6',
                \Graphpinator\Typesystem\Container::String()->notNull(),
                static function (int $parent, ?string $arg1, ?int $arg2, ?bool $arg3) : string {
                    return 'abc';
                },
            )->setArguments(new \Graphpinator\Typesystem\Argument\ArgumentSet([
                \Graphpinator\Typesystem\Argument\Argument::create('arg1', \Graphpinator\Typesystem\Container::String()),
                \Graphpinator\Typesystem\Argument\Argument::create('arg2', \Graphpinator\Typesystem\Container::Int()),
                \Graphpinator\Typesystem\Argument\Argument::create('arg3', \Graphpinator\Typesystem\Container::Boolean()),
            ])),
        ]);
    }
}