<?php

declare(strict_types = 1);

namespace Graphpinator\PersistedQueries\Tests;

final class ResolverValueTest extends \PHPUnit\Framework\TestCase
{
    public static function getQuery() : \Graphpinator\Typesystem\Type
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
                        \Graphpinator\Typesystem\Container::String()->notNull(),
                        static function ($parent) : string {
                            return 'test';
                        },
                    )->setArguments(new \Graphpinator\Typesystem\Argument\ArgumentSet([
                        \Graphpinator\Typesystem\Argument\Argument::create(
                            'arg',
                            \Graphpinator\Typesystem\Container::String()->notNull(),
                        ),
                    ])),
                ]);
            }
        };
    }

    public function testSimple() : void
    {
        $container = new \Graphpinator\SimpleContainer([self::getQuery()], []);
        $schema = new \Graphpinator\Typesystem\Schema($container, self::getQuery());
        $cache = [];
        $module = new \Graphpinator\PersistedQueries\PersistedQueriesModule(
            $schema,
            new \Graphpinator\PersistedQueries\Tests\ArrayCache($cache),
        );
        $request = new \Graphpinator\Request\Request('query abc');

        $module->processRequest($request);
        $module->processNormalized($this->abc());

        $this->assertArrayHasKey(3834652180, $cache);
        $this->assertEquals(
            '[{"type":"query","name":null,"selectionSet":[{"selectionType":"Graphpinator\\\Normalizer\\\Selection\\\Field","fieldName":'
            . '"field","alias":"field","argumentValueSet":[{"argument":"arg"'
            . ',"value":{"valueType":"Graphpinator\\\Value\\\ScalarValue","type":{"type":"named","name":"String"},"value":"abc","resolverValue":"O:8:'
            . '\"DateTime\":3:{s:4:\"date\";s:26:\"2021-06-29 00:00:00.000000\";s:13:\"timezone_type\";i:3;s:8:\"timezone\";s:3:\"UTC\";}"}}],'
            . '"directiveSet":[],"selectionSet":null}],"variableSet":[],"directiveSet":[]}]',
            $cache[3834652180],
        );

        $result = $module->processRequest($request);
        $this->assertInstanceOf(\Graphpinator\Normalizer\NormalizedRequest::class, $result);
        $argumentValue = $result->getOperations()->getFirst()->getSelections()->getFirst()->getArguments()->getFirst()->getValue();
        $this->assertTrue($argumentValue->hasResolverValue());
        $this->assertInstanceOf(\DateTime::class, $argumentValue->getResolverValue());
        $this->assertEquals(new \DateTime('2021-06-29'), $argumentValue->getResolverValue());
        $this->assertSame('abc', $argumentValue->getRawValue());
    }

    public function abc() : \Graphpinator\Normalizer\NormalizedRequest
    {
        $value = new \Graphpinator\Value\ScalarValue(\Graphpinator\Typesystem\Container::String(), 'abc', true);
        $value->setResolverValue(new \DateTime('2021-06-29'));

        return new \Graphpinator\Normalizer\NormalizedRequest(
            new \Graphpinator\Normalizer\Operation\OperationSet([
                new \Graphpinator\Normalizer\Operation\Operation(
                    'query',
                    null,
                    self::getQuery(),
                    new \Graphpinator\Normalizer\Selection\SelectionSet([
                        new \Graphpinator\Normalizer\Selection\Field(
                            self::getQuery()->getFields()['field'],
                            'field',
                            new \Graphpinator\Value\ArgumentValueSet([
                                new \Graphpinator\Value\ArgumentValue(
                                    \Graphpinator\Typesystem\Argument\Argument::create(
                                        'arg',
                                        \Graphpinator\Typesystem\Container::String()->notNull(),
                                    ),
                                    $value,
                                    false,
                                ),
                            ]),
                            new \Graphpinator\Normalizer\Directive\DirectiveSet(),
                        ),
                    ]),
                    new \Graphpinator\Normalizer\Variable\VariableSet(),
                    new \Graphpinator\Normalizer\Directive\DirectiveSet(),
                ),
            ]),
        );
    }
}
