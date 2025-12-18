<?php

declare(strict_types = 1);

namespace Graphpinator\PersistedQueries;

use Graphpinator\Normalizer\Directive\Directive;
use Graphpinator\Normalizer\Directive\DirectiveSet;
use Graphpinator\Normalizer\NormalizedRequest;
use Graphpinator\Normalizer\Operation\Operation;
use Graphpinator\Normalizer\Operation\OperationSet;
use Graphpinator\Normalizer\Selection\Field;
use Graphpinator\Normalizer\Selection\FragmentSpread;
use Graphpinator\Normalizer\Selection\InlineFragment;
use Graphpinator\Normalizer\Selection\SelectionSet;
use Graphpinator\Normalizer\Variable\Variable;
use Graphpinator\Normalizer\Variable\VariableSet;
use Graphpinator\Normalizer\Visitor\GetFieldVisitor;
use Graphpinator\Parser\OperationType;
use Graphpinator\Typesystem\Argument\ArgumentSet;
use Graphpinator\Typesystem\Contract\ExecutableDirective;
use Graphpinator\Typesystem\Contract\NamedType;
use Graphpinator\Typesystem\Contract\Type;
use Graphpinator\Typesystem\InputType;
use Graphpinator\Typesystem\ListType;
use Graphpinator\Typesystem\NotNullType;
use Graphpinator\Typesystem\Schema;
use Graphpinator\Typesystem\Visitor\GetNamedTypeVisitor;
use Graphpinator\Value\ArgumentValue;
use Graphpinator\Value\ArgumentValueSet;
use Graphpinator\Value\EnumValue;
use Graphpinator\Value\InputedValue;
use Graphpinator\Value\InputValue;
use Graphpinator\Value\ListInputedValue;
use Graphpinator\Value\NullValue;
use Graphpinator\Value\ScalarValue;
use Graphpinator\Value\VariableValue;
use Infinityloop\Utils\Json;

final class Deserializer
{
    /** @var \SplStack<NamedType> */
    private \SplStack $typeStack;
    private VariableSet $currentVariableSet;

    public function __construct(
        private readonly Schema $schema,
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
        $operationType = OperationType::from($operation->type);
        $rootType = match ($operationType) {
            OperationType::QUERY => $this->schema->getQuery(),
            OperationType::MUTATION => $this->schema->getMutation(),
            OperationType::SUBSCRIPTION => $this->schema->getSubscription(),
        };
        $this->typeStack->push($rootType);
        $variables = $this->deserializeVariableSet($operation->variableSet);

        $return = new Operation(
            $operationType,
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
                default => throw new \LogicException($selection->selectionType),
            };
        }

        return new SelectionSet($temp);
    }

    private function deserializeField(\stdClass $field) : Field
    {
        $parentType = $this->typeStack->top();
        $fieldDef = $parentType->accept(new GetFieldVisitor($field->fieldName));

        $this->typeStack->push($fieldDef->getType()->accept(new GetNamedTypeVisitor()));

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
        \assert($typeCond instanceof NamedType);

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
        \assert($typeCond instanceof NamedType);

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
        $this->typeStack->push($argument->getType()->accept(new GetNamedTypeVisitor()));

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
            default => throw new \LogicException($type->type),
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
            NullValue::class => new NullValue($this->deserializeType($inputedValue->type)),
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
            default => throw new \LogicException($inputedValue->valueType),
        };
    }
}
