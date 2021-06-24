<?php

declare(strict_types = 1);

namespace Graphpinator\PersistedQueries;

final class Serializer
{
    use \Nette\SmartObject;

    public function serializeNormalizedRequest(\Graphpinator\Normalizer\NormalizedRequest $normalizedRequest) : string
    {
        return \Infinityloop\Utils\Json::fromNative($this->serializeOperationSet($normalizedRequest->getOperations()))->toString();
    }

    public function serializeOperationSet(\Graphpinator\Normalizer\Operation\OperationSet $operationSet) : array
    {
        $temp = [];

        foreach ($operationSet as $operation) {
            $temp[] = $this->serializeOperation($operation);
        }

        return $temp;
    }

    public function serializeOperation(\Graphpinator\Normalizer\Operation\Operation $operation) : array
    {
        return [
            'type' => $operation->getType(),
            'name' => $operation->getName(),
            'fieldSet' => $this->serializeFieldSet($operation->getFields()),
            'variableSet' => $this->serializeVariableSet($operation->getVariables()),
            'directiveSet' => $this->serializeDirectiveSet($operation->getDirectives()),
        ];
    }

    public function serializeDirectiveSet(\Graphpinator\Normalizer\Directive\DirectiveSet $set) : array
    {
        $temp = [];

        foreach ($set as $directive) {
            $temp[] = $this->serializeDirective($directive);
        }

        return $temp;
    }

    public function serializeDirective(\Graphpinator\Normalizer\Directive\Directive $directive) : array
    {
        return [
            'directive' => $directive->getDirective()->getName(),
            'arguments' => $this->serializeArgumentValueSet($directive->getArguments()),
        ];
    }

    public function serializeVariableSet(\Graphpinator\Normalizer\Variable\VariableSet $set) : array
    {
        $temp = [];

        foreach ($set as $variable) {
            $temp[] = $this->serializeVariable($variable);
        }

        return $temp;
    }

    public function serializeVariable(\Graphpinator\Normalizer\Variable\Variable $variable) : array
    {
        return [
            'name' => $variable->getName(),
            'type' => $this->serializeType($variable->getType()),
            'defaultValue' => $this->serializeInputedValue($variable->getDefaultValue()),
        ];
    }

    public function serializeFieldSet(\Graphpinator\Normalizer\Field\FieldSet $fieldSet) : array
    {
        $temp = [];

        foreach ($fieldSet as $field) {
            $temp[] = $this->serializeField($field);
        }

        return $temp;
    }

    public function serializeField(\Graphpinator\Normalizer\Field\Field $field) : array
    {
        return [
            'fieldName' => $field->getField()->getName(),
            'alias' => $field->getAlias(),
            'argumentValueSet' => $this->serializeArgumentValueSet($field->getArguments()),
            'directiveSet' => $this->serializeDirectiveSet($field->getDirectives()),
            'fieldSet' => $field->getFields() === null
                ? null
                : $this->serializeFieldSet($field->getFields()),
            'typeCond' => $field->getTypeCondition()?->getName(),
        ];
    }

    public function serializeArgumentValueSet(\Graphpinator\Value\ArgumentValueSet $argumentValueSet) : array
    {
        $temp = [];

        foreach ($argumentValueSet as $argumentValue) {
            $temp[] = $this->serializeargumentValue($argumentValue);
        }

        return $temp;
    }

    public function serializeArgumentValue(\Graphpinator\Value\ArgumentValue $argumentValue) : array
    {
        return [
            'argument' => $argumentValue->getArgument()->getName(),
            'value' => $this->serializeInputedValue($argumentValue->getValue()),
        ];
    }

    public function serializeInputedValue(\Graphpinator\Value\InputedValue $inputedValue) : array
    {
        $data = [
            'valueType' => $inputedValue::class,
            'type' => $this->serializeType($inputedValue->getType()),
        ];

        switch ($inputedValue::class) {
            case \Graphpinator\Value\NullInputedValue::class:
                break;
            case \Graphpinator\Value\ScalarValue::class:
                $data['value'] = $inputedValue->getRawValue();

                if ($inputedValue->hasResolverValue()) {
                    $data['resolverValue'] = \serialize($inputedValue->getResolverValue());
                }

                break;
            case \Graphpinator\Value\EnumValue::class:
                $data['value'] = $inputedValue->getRawValue();
                break;
            case \Graphpinator\Value\VariableValue::class:
                $data['variableName'] = $inputedValue->getVariable()->getName();
                break;
            case \Graphpinator\Value\ListInputedValue::class:
                $inner = [];

                foreach ($inputedValue as $item) {
                    \assert($item instanceof \Graphpinator\Value\InputedValue);
                    $inner[] = $this->serializeInputedValue($item);
                }

                $data['inner'] = $inner;
                break;
            case \Graphpinator\Value\InputValue::class:
                $inner = [];

                foreach ($inputedValue as $key => $item) {
                    \assert($item instanceof \Graphpinator\Value\ArgumentValue);
                    $inner[$key] = $this->serializeArgumentValue($item);
                }

                $data['inner'] = $inner;
                break;
        }

        return $data;
    }

    public function serializeType(\Graphpinator\Typesystem\Contract\Type $type) : array
    {
        if ($type instanceof \Graphpinator\Typesystem\ListType) {
            return [
                'type' => 'list',
                'inner' => $this->serializeType($type->getInnerType()),
            ];
        }

        if ($type instanceof \Graphpinator\Typesystem\Contract\NamedType) {
            return [
                'type' => 'named',
                'name' => $type->getNamedType()->getName(),
            ];
        }

        if ($type instanceof \Graphpinator\Typesystem\NotNullType) {
            return [
                'type' => 'notnull',
                'inner' => $this->serializeType($type->getInnerType()),
            ];
        }
    }
}
