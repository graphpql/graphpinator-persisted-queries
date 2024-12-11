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
use Graphpinator\Typesystem\Contract\Type;
use Graphpinator\Typesystem\Contract\TypeConditionable;
use Graphpinator\Typesystem\ListType;
use Graphpinator\Typesystem\NotNullType;
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

final class Serializer
{
    public function serializeNormalizedRequest(NormalizedRequest $normalizedRequest) : string
    {
        return Json::fromNative($this->serializeOperationSet($normalizedRequest->getOperations()))->toString();
    }

    private function serializeOperationSet(OperationSet $operationSet) : array
    {
        $temp = [];

        foreach ($operationSet as $operation) {
            $temp[] = $this->serializeOperation($operation);
        }

        return $temp;
    }

    private function serializeOperation(Operation $operation) : array
    {
        return [
            'type' => $operation->getType(),
            'name' => $operation->getName(),
            'selectionSet' => $this->serializeSelectionSet($operation->getSelections()),
            'variableSet' => $this->serializeVariableSet($operation->getVariables()),
            'directiveSet' => $this->serializeDirectiveSet($operation->getDirectives()),
        ];
    }

    private function serializeDirectiveSet(DirectiveSet $set) : array
    {
        $temp = [];

        foreach ($set as $directive) {
            $temp[] = $this->serializeDirective($directive);
        }

        return $temp;
    }

    private function serializeDirective(Directive $directive) : array
    {
        return [
            'directive' => $directive->getDirective()->getName(),
            'arguments' => $this->serializeArgumentValueSet($directive->getArguments()),
        ];
    }

    private function serializeVariableSet(VariableSet $set) : array
    {
        $temp = [];

        foreach ($set as $variable) {
            $temp[] = $this->serializeVariable($variable);
        }

        return $temp;
    }

    private function serializeVariable(Variable $variable) : array
    {
        return [
            'name' => $variable->getName(),
            'type' => $this->serializeType($variable->getType()),
            'defaultValue' => $variable->getDefaultValue() === null
                ? null
                : $this->serializeInputedValue($variable->getDefaultValue()),
        ];
    }

    private function serializeSelectionSet(SelectionSet $selectionSet) : array
    {
        $temp = [];

        foreach ($selectionSet as $selection) {
            $temp[] = match ($selection::class) {
                Field::class => $this->serializeField($selection),
                FragmentSpread::class => $this->serializeFragmentSpread($selection),
                InlineFragment::class => $this->serializeInlineFragment($selection),
            };
        }

        return $temp;
    }

    private function serializeField(Field $field) : array
    {
        return [
            'selectionType' => Field::class,
            'fieldName' => $field->getField()->getName(),
            'alias' => $field->getOutputName(),
            'argumentValueSet' => $this->serializeArgumentValueSet($field->getArguments()),
            'directiveSet' => $this->serializeDirectiveSet($field->getDirectives()),
            'selectionSet' => $field->getSelections() === null
                ? null
                : $this->serializeSelectionSet($field->getSelections()),
        ];
    }

    private function serializeFragmentSpread(FragmentSpread $fragmentSpread) : array
    {
        return [
            'selectionType' => FragmentSpread::class,
            'fragmentName' => $fragmentSpread->getName(),
            'selectionSet' => $this->serializeSelectionSet($fragmentSpread->getSelections()),
            'directiveSet' => $this->serializeDirectiveSet($fragmentSpread->getDirectives()),
            'typeCond' => $this->serializeType($fragmentSpread->getTypeCondition()),
        ];
    }

    private function serializeInlineFragment(InlineFragment $inlineFragment) : array
    {
        return [
            'selectionType' => InlineFragment::class,
            'selectionSet' => $this->serializeSelectionSet($inlineFragment->getSelections()),
            'directiveSet' => $this->serializeDirectiveSet($inlineFragment->getDirectives()),
            'typeCond' => $inlineFragment->getTypeCondition() instanceof TypeConditionable
                ? $this->serializeType($inlineFragment->getTypeCondition())
                : null,
        ];
    }

    private function serializeArgumentValueSet(ArgumentValueSet $argumentValueSet) : array
    {
        $temp = [];

        foreach ($argumentValueSet as $argumentValue) {
            $temp[] = $this->serializeargumentValue($argumentValue);
        }

        return $temp;
    }

    private function serializeArgumentValue(ArgumentValue $argumentValue) : array
    {
        return [
            'argument' => $argumentValue->getArgument()->getName(),
            'value' => $this->serializeInputedValue($argumentValue->getValue()),
        ];
    }

    private function serializeInputedValue(InputedValue $inputedValue) : array
    {
        $data = [
            'valueType' => $inputedValue::class,
            'type' => $this->serializeType($inputedValue->getType()),
        ];

        switch ($inputedValue::class) {
            case NullInputedValue::class:
                break;
            case ScalarValue::class:
                $data['value'] = $inputedValue->getRawValue();

                if ($inputedValue->hasResolverValue()) {
                    $data['resolverValue'] = \serialize($inputedValue->getResolverValue());
                }

                break;
            case EnumValue::class:
                $data['value'] = $inputedValue->getRawValue();

                break;
            case VariableValue::class:
                $data['variableName'] = $inputedValue->getVariable()->getName();

                break;
            case ListInputedValue::class:
                $inner = [];

                foreach ($inputedValue as $item) {
                    \assert($item instanceof InputedValue);
                    $inner[] = $this->serializeInputedValue($item);
                }

                $data['inner'] = $inner;

                break;
            case InputValue::class:
                $inner = [];

                foreach ($inputedValue as $key => $item) {
                    \assert($item instanceof ArgumentValue);
                    $inner[$key] = $this->serializeArgumentValue($item);
                }

                $data['inner'] = $inner;

                break;
        }

        return $data;
    }

    private function serializeType(Type $type) : array
    {
        return match ($type::class) {
            ListType::class => [
                'type' => 'list',
                'inner' => $this->serializeType($type->getInnerType()),
            ],
            NotNullType::class => [
                'type' => 'notnull',
                'inner' => $this->serializeType($type->getInnerType()),
            ],
            default => [
                'type' => 'named',
                'name' => $type->getNamedType()->getName(),
            ],
        };
    }
}
