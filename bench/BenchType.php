<?php

declare(strict_types = 1);

namespace Graphpinator\PersistedQueries\Bench;

use Graphpinator\PersistedQueries\Tests\VariableTest;
use Graphpinator\Typesystem\Argument\Argument;
use Graphpinator\Typesystem\Argument\ArgumentSet;
use Graphpinator\Typesystem\Container;
use Graphpinator\Typesystem\Field\ResolvableField;
use Graphpinator\Typesystem\Field\ResolvableFieldSet;
use Graphpinator\Typesystem\Type;

class BenchType extends Type
{
    protected const NAME = 'type';

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
                VariableTest::getSimpleEnum()->notNull(),
                static function ($parent, string $val) : string {
                    return $val;
                },
            )->setArguments(new ArgumentSet([
                new Argument(
                    'val',
                    VariableTest::getSimpleEnum()->notNull(),
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
                    VariableTest::getSimpleInput()->notNull(),
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
}