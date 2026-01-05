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
use Graphpinator\Typesystem\Visitor\GetNamedTypeVisitor;
use Graphpinator\Value\ArgumentValue;
use Graphpinator\Value\ArgumentValueSet;
use Graphpinator\Value\Contract\InputedValue;
use Graphpinator\Value\EnumValue;
use Graphpinator\Value\InputValue;
use Graphpinator\Value\ListValue;
use Graphpinator\Value\NullValue;
use Graphpinator\Value\ScalarValue;
use Graphpinator\Value\VariableValue;
use Infinityloop\Utils\Json;

final class Serializer
{
    public function serializeNormalizedRequest(NormalizedRequest $normalizedRequest) : string
    {
        return Json::fromNative($this->serializeOperationSet($normalizedRequest->operations))->toString();
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
            'type' => $operation->type,
            'name' => $operation->name,
            'selectionSet' => $this->serializeSelectionSet($operation->children),
            'variableSet' => $this->serializeVariableSet($operation->variables),
            'directiveSet' => $this->serializeDirectiveSet($operation->directives),
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
            'directive' => $directive->directive->getName(),
            'arguments' => $this->serializeArgumentValueSet($directive->arguments),
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
            'name' => $variable->name,
            'type' => $this->serializeType($variable->type),
            'defaultValue' => $variable->defaultValue === null
                ? null
                : $this->serializeInputedValue($variable->defaultValue),
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
                default => throw new \LogicException('Unknown selection set item: ' . $selection::class),
            };
        }

        return $temp;
    }

    private function serializeField(Field $field) : array
    {
        return [
            'selectionType' => Field::class,
            'fieldName' => $field->field->getName(),
            'alias' => $field->outputName,
            'argumentValueSet' => $this->serializeArgumentValueSet($field->arguments),
            'directiveSet' => $this->serializeDirectiveSet($field->directives),
            'selectionSet' => $field->children === null
                ? null
                : $this->serializeSelectionSet($field->children),
        ];
    }

    private function serializeFragmentSpread(FragmentSpread $fragmentSpread) : array
    {
        return [
            'selectionType' => FragmentSpread::class,
            'fragmentName' => $fragmentSpread->name,
            'selectionSet' => $this->serializeSelectionSet($fragmentSpread->children),
            'directiveSet' => $this->serializeDirectiveSet($fragmentSpread->directives),
            'typeCond' => $this->serializeType($fragmentSpread->typeCondition),
        ];
    }

    private function serializeInlineFragment(InlineFragment $inlineFragment) : array
    {
        return [
            'selectionType' => InlineFragment::class,
            'selectionSet' => $this->serializeSelectionSet($inlineFragment->children),
            'directiveSet' => $this->serializeDirectiveSet($inlineFragment->directives),
            'typeCond' => $inlineFragment->typeCondition instanceof TypeConditionable
                ? $this->serializeType($inlineFragment->typeCondition)
                : null,
        ];
    }

    private function serializeArgumentValueSet(ArgumentValueSet $argumentValueSet) : array
    {
        $temp = [];

        foreach ($argumentValueSet as $argumentValue) {
            $temp[] = $this->serializeArgumentValue($argumentValue);
        }

        return $temp;
    }

    private function serializeArgumentValue(ArgumentValue $argumentValue) : array
    {
        return [
            'argument' => $argumentValue->argument->getName(),
            'value' => $this->serializeInputedValue($argumentValue->value),
        ];
    }

    private function serializeInputedValue(InputedValue $inputedValue) : array
    {
        $data = [
            'valueType' => $inputedValue::class,
            'type' => $this->serializeType($inputedValue->getType()),
        ];

        switch ($inputedValue::class) {
            case NullValue::class:
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
                $data['variableName'] = $inputedValue->variable->name;

                break;
            case ListValue::class:
                $inner = [];

                foreach ($inputedValue as $item) {
                    $inner[] = $this->serializeInputedValue($item);
                }

                $data['inner'] = $inner;

                break;
            case InputValue::class:
                $inner = [];

                foreach ($inputedValue as $key => $item) {
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
                'name' => $type->accept(new GetNamedTypeVisitor())->getName(),
            ],
        };
    }
}
