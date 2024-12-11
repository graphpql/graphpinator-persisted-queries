<?php

declare(strict_types = 1);

namespace Graphpinator\PersistedQueries\Tests;

use Graphpinator\Normalizer\Directive\DirectiveSet;
use Graphpinator\Normalizer\NormalizedRequest;
use Graphpinator\Normalizer\Operation\Operation;
use Graphpinator\Normalizer\Operation\OperationSet;
use Graphpinator\Normalizer\Selection\Field;
use Graphpinator\Normalizer\Selection\SelectionSet;
use Graphpinator\Normalizer\Variable\VariableSet;
use Graphpinator\PersistedQueries\PersistedQueriesModule;
use Graphpinator\Request\Request;
use Graphpinator\SimpleContainer;
use Graphpinator\Typesystem\Argument\Argument;
use Graphpinator\Typesystem\Argument\ArgumentSet;
use Graphpinator\Typesystem\Container;
use Graphpinator\Typesystem\Field\ResolvableField;
use Graphpinator\Typesystem\Field\ResolvableFieldSet;
use Graphpinator\Typesystem\Schema;
use Graphpinator\Typesystem\Type;
use Graphpinator\Value\ArgumentValue;
use Graphpinator\Value\ArgumentValueSet;
use Graphpinator\Value\ScalarValue;
use PHPUnit\Framework\TestCase;

final class ResolverValueTest extends TestCase
{
    public static function getQuery() : Type
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
                        Container::String()->notNull(),
                        static function ($parent) : string {
                            return 'test';
                        },
                    )->setArguments(new ArgumentSet([
                        Argument::create(
                            'arg',
                            Container::String()->notNull(),
                        ),
                    ])),
                ]);
            }
        };
    }

    public function testSimple() : void
    {
        $container = new SimpleContainer([self::getQuery()], []);
        $schema = new Schema($container, self::getQuery());
        $cache = [];
        $module = new PersistedQueriesModule(
            $schema,
            new ArrayCache($cache),
        );
        $request = new Request('query abc');

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
        $this->assertInstanceOf(NormalizedRequest::class, $result);
        $argumentValue = $result->getOperations()->getFirst()->getSelections()->getFirst()->getArguments()->getFirst()->getValue();
        $this->assertTrue($argumentValue->hasResolverValue());
        $this->assertInstanceOf(\DateTime::class, $argumentValue->getResolverValue());
        $this->assertEquals(new \DateTime('2021-06-29'), $argumentValue->getResolverValue());
        $this->assertSame('abc', $argumentValue->getRawValue());
    }

    public function abc() : NormalizedRequest
    {
        $value = new ScalarValue(Container::String(), 'abc', true);
        $value->setResolverValue(new \DateTime('2021-06-29'));

        return new NormalizedRequest(
            new OperationSet([
                new Operation(
                    'query',
                    null,
                    self::getQuery(),
                    new SelectionSet([
                        new Field(
                            self::getQuery()->getFields()['field'],
                            'field',
                            new ArgumentValueSet([
                                new ArgumentValue(
                                    Argument::create(
                                        'arg',
                                        Container::String()->notNull(),
                                    ),
                                    $value,
                                    false,
                                ),
                            ]),
                            new DirectiveSet(),
                        ),
                    ]),
                    new VariableSet(),
                    new DirectiveSet(),
                ),
            ]),
        );
    }
}
