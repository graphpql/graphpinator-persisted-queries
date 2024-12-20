<?php

declare(strict_types = 1);

namespace Graphpinator\PersistedQueries;

use Graphpinator\Normalizer\Directive\Directive;
use Graphpinator\Normalizer\Directive\DirectiveSet;
use Graphpinator\Normalizer\GetFieldVisitor;
use Graphpinator\Normalizer\NormalizedRequest;
use Graphpinator\Normalizer\Operation\Operation;
use Graphpinator\Normalizer\Operation\OperationSet;
use Graphpinator\Normalizer\Selection\Field;
use Graphpinator\Normalizer\Selection\FragmentSpread;
use Graphpinator\Normalizer\Selection\InlineFragment;
use Graphpinator\Normalizer\Selection\SelectionSet;
use Graphpinator\Normalizer\Variable\Variable;
use Graphpinator\Normalizer\Variable\VariableSet;
use Graphpinator\Tokenizer\TokenType;
use Graphpinator\Typesystem\Argument\ArgumentSet;
use Graphpinator\Typesystem\Contract\ExecutableDirective;
use Graphpinator\Typesystem\Contract\Type;
use Graphpinator\Typesystem\Contract\TypeConditionable;
use Graphpinator\Typesystem\InputType;
use Graphpinator\Typesystem\ListType;
use Graphpinator\Typesystem\NotNullType;
use Graphpinator\Typesystem\Schema;
use Graphpinator\Value\ArgumentValue;
use Graphpinator\Value\ArgumentValueSet;
use Graphpinator\Value\EnumValue;
use Graphpinator\Value\InputValue;
use Graphpinator\Value\InputedValue;
use Graphpinator\Value\ListInputedValue;
use Graphpinator\Value\NullInputedValue;
use Graphpinator\Value\ScalarValue;
use Graphpinator\Value\VariableValue;
use Infinityloop\Utils\Json;

final class Deserializer
{
    private \SplStack $typeStack;
    private VariableSet $currentVariableSet;

    public function __construct(
        private Schema $schema,
    )
    {
        $this->typeStack = new \SplStack();
    }

    public function deserializeNormalizedRequest(string $data) : NormalizedRequest
    {
        $operationSet = Json::fromString($data)->toNative();
        \assert(\is_array($operationSet));

        return new NormalizedRequest($this->deserializeOperationSet($operationSet));
    }

    private function deserializeOperationSet(array $operationSet) : OperationSet
    {
        $temp = [];

        foreach ($operationSet as $operation) {
            \assert($this->typeStack->isEmpty());
            $temp[] = $this->deserializeOperation($operation);
        }

        return new OperationSet($temp);
    }

    private function deserializeOperation(\stdClass $operation) : Operation
    {
        $rootType = match ($operation->type) {
            TokenType::QUERY->value => $this->schema->getQuery(),
            TokenType::MUTATION->value => $this->schema->getMutation(),
            TokenType::SUBSCRIPTION->value => $this->schema->getSubscription(),
        };
        $this->typeStack->push($rootType);
        $variables = $this->deserializeVariableSet($operation->variableSet);

        $return = new Operation(
            $operation->type,
            $operation->name,
            $rootType,
            $this->deserializeSelectionSet($operation->selectionSet),
            $variables,
            $this->deserializeDirectiveSet($operation->directiveSet),
        );

        $this->typeStack->pop();

        return $return;
    }

    private function deserializeSelectionSet(array $selectionSet) : SelectionSet
    {
        $temp = [];

        foreach ($selectionSet as $selection) {
            $temp[] = match ($selection->selectionType) {
                Field::class => $this->deserializeField($selection),
                FragmentSpread::class => $this->deserializeFragmentSpread($selection),
                InlineFragment::class => $this->deserializeInlineFragment($selection),
            };
        }

        return new SelectionSet($temp);
    }

    private function deserializeField(\stdClass $field) : Field
    {
        $parentType = $this->typeStack->top();
        \assert($parentType instanceof Type);
        $fieldDef = $parentType->accept(new GetFieldVisitor($field->fieldName));

        $this->typeStack->push($fieldDef->getType()->getNamedType());

        $return = new Field(
            $fieldDef,
            $field->alias,
            $this->deserializeArgumentValueSet($field->argumentValueSet, $fieldDef->getArguments()),
            $this->deserializeDirectiveSet($field->directiveSet),
            $field->selectionSet === null
                ? null
                : $this->deserializeSelectionSet($field->selectionSet),
        );

        $this->typeStack->pop();

        return $return;
    }

    private function deserializeFragmentSpread(\stdClass $fragmentSpread) : FragmentSpread
    {
        $typeCond = $this->deserializeType($fragmentSpread->typeCond);
        \assert($typeCond instanceof TypeConditionable);

        $this->typeStack->push($typeCond);

        $return = new FragmentSpread(
            $fragmentSpread->fragmentName,
            $this->deserializeSelectionSet($fragmentSpread->selectionSet),
            $this->deserializeDirectiveSet($fragmentSpread->directiveSet),
            $typeCond,
        );

        $this->typeStack->pop();

        return $return;
    }

    private function deserializeInlineFragment(\stdClass $inlineSpread) : InlineFragment
    {
        $typeCond = $this->deserializeType($inlineSpread->typeCond);
        \assert($typeCond instanceof TypeConditionable);

        $this->typeStack->push($typeCond);

        $return = new InlineFragment(
            $this->deserializeSelectionSet($inlineSpread->selectionSet),
            $this->deserializeDirectiveSet($inlineSpread->directiveSet),
            $typeCond,
        );

        $this->typeStack->pop();

        return $return;
    }

    private function deserializeDirectiveSet(array $directiveSet) : DirectiveSet
    {
        $temp = [];

        foreach ($directiveSet as $directive) {
            $temp[] = $this->deserializeDirective($directive);
        }

        return new DirectiveSet($temp);
    }

    private function deserializeDirective(\stdClass $directive) : Directive
    {
        $directiveDef = $this->schema->getContainer()->getDirective($directive->directive);
        \assert($directiveDef instanceof ExecutableDirective);

        return new Directive(
            $directiveDef,
            $this->deserializeArgumentValueSet($directive->arguments, $directiveDef->getArguments()),
        );
    }

    private function deserializeArgumentValueSet(array $argumentValueSet, ArgumentSet $currentArguments) : ArgumentValueSet
    {
        $temp = [];

        foreach ($argumentValueSet as $argumentValue) {
            $temp[] = $this->deserializeArgumentValue($argumentValue, $currentArguments);
        }

        return new ArgumentValueSet($temp);
    }

    private function deserializeArgumentValue(\stdClass $argumentValue, ArgumentSet $currentArguments) : ArgumentValue
    {
        $argument = $currentArguments->offsetGet($argumentValue->argument);
        $this->typeStack->push($argument->getType());

        $return = new ArgumentValue(
            $argument,
            $this->deserializeInputedValue($argumentValue->value),
            true,
        );

        $this->typeStack->pop();

        return $return;
    }

    private function deserializeVariableSet(array $variableSet) : VariableSet
    {
        $temp = [];

        foreach ($variableSet as $variable) {
            $temp[] = $this->deserializeVariable($variable);
        }

        $set = new VariableSet($temp);
        $this->currentVariableSet = $set;

        return $set;
    }

    private function deserializeVariable(\stdClass $variable) : Variable
    {
        return new Variable(
            $variable->name,
            $this->deserializeType($variable->type),
            $variable->defaultValue === null
                ? null
                : $this->deserializeInputedValue($variable->defaultValue),
        );
    }

    private function deserializeType(\stdClass $type) : Type
    {
        return match ($type->type) {
            'named' => $this->schema->getContainer()->getType($type->name),
            'list' => new ListType($this->deserializeType($type->inner)),
            'notnull' => new NotNullType($this->deserializeType($type->inner)),
        };
    }

    private function deserializeScalarValue(\stdClass $inputedValue) : ScalarValue
    {
        $scalarValue = new ScalarValue(
            $this->deserializeType($inputedValue->type),
            $inputedValue->value,
            false,
        );

        if (isset($inputedValue->resolverValue)) {
            $scalarValue->setResolverValue(\unserialize($inputedValue->resolverValue));
        }

        return $scalarValue;
    }

    private function deserializeListInputedValue(\stdClass $inputedValue) : ListInputedValue
    {
        $inner = [];

        foreach ($inputedValue->inner as $item) {
            $inner[] = $this->deserializeInputedValue($item);
        }

        return new ListInputedValue(
            $this->deserializeType($inputedValue->type),
            $inner,
        );
    }

    private function deserializeInputValue(\stdClass $inputedValue) : InputValue
    {
        $inner = [];
        $type = $this->deserializeType($inputedValue->type);
        \assert($type instanceof InputType);

        foreach ($inputedValue->inner as $key => $item) {
            $inner[$key] = $this->deserializeArgumentValue($item, $type->getArguments());
        }

        return new InputValue(
            $type,
            (object) $inner,
        );
    }

    private function deserializeInputedValue(\stdClass $inputedValue) : InputedValue
    {
        return match ($inputedValue->valueType) {
            NullInputedValue::class => new NullInputedValue($this->deserializeType($inputedValue->type)),
            ScalarValue::class => $this->deserializeScalarValue($inputedValue),
            EnumValue::class => new EnumValue(
                $this->deserializeType($inputedValue->type),
                $inputedValue->value,
                false,
            ),
            VariableValue::class => new VariableValue(
                $this->deserializeType($inputedValue->type),
                $this->currentVariableSet->offsetGet($inputedValue->variableName),
            ),
            ListInputedValue::class => $this->deserializeListInputedValue($inputedValue),
            InputValue::class => $this->deserializeInputValue($inputedValue),
        };
    }
}
