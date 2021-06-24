<?php

declare(strict_types = 1);

namespace Graphpinator\PersistedQueries;

use Graphpinator\Value\ArgumentValue;
use Graphpinator\Value\EnumValue;
use Graphpinator\Value\InputedValue;
use Graphpinator\Value\InputValue;
use Graphpinator\Value\ListInputedValue;
use Graphpinator\Value\ListValue;
use Graphpinator\Value\NullInputedValue;
use Graphpinator\Value\ScalarValue;
use Graphpinator\Value\VariableValue;

final class Serializer
{
    use \Nette\SmartObject;

    public function serializeNormalizedRequest(\Graphpinator\Normalizer\NormalizedRequest $normalizedRequest) : string
    {
        return $this->serializeOperationSet($normalizedRequest->getOperations());
    }

    public function serializeOperationSet(\Graphpinator\Normalizer\Operation\OperationSet $operationSet) : string
    {
        $temp = [];

        foreach ($operationSet as $operation) {
            $temp[] = $this->serializeOperation($operation);
        }

        return \Infinityloop\Utils\Json::fromNative($temp)->toString();
    }

    public function serializeOperation(\Graphpinator\Normalizer\Operation\Operation $operation) : string
    {
        return \Infinityloop\Utils\Json::fromNative([
            'type' => $operation->getType(),
            'name' => $operation->getName(),
            'fieldSet' => $this->serializeFieldSet($operation->getFields()),
            'variableSet' => $this->serializeVariableSet($operation->getVariables()),
            'directiveSet' => $this->serializeDirectiveSet($operation->getDirectives()),
        ])->toString();
    }

    public function serializeDirectiveSet(\Graphpinator\Normalizer\Directive\DirectiveSet $set) : string
    {
        $temp = [];

        foreach ($set as $directive) {
            $temp[] = $this->serializeDirective($directive);
        }

        return \Infinityloop\Utils\Json::fromNative($temp)->toString();
    }

    public function serializeDirective(\Graphpinator\Normalizer\Directive\Directive $directive) : string
    {
        return \Infinityloop\Utils\Json::fromNative([
            'directive' => $directive->getDirective()->getName(),
            'arguments' => $this->serializeArgumentValueSet($directive->getArguments()),
        ])->toString();
    }

    public function serializeVariableSet(\Graphpinator\Normalizer\Variable\VariableSet $set) : string
    {
        $temp = [];

        foreach ($set as $variable) {
            $temp[] = $this->serializeVariable($variable);
        }

        return \Infinityloop\Utils\Json::fromNative($temp)->toString();
    }

    public function serializeVariable(\Graphpinator\Normalizer\Variable\Variable $variable) : string
    {
        return \Infinityloop\Utils\Json::fromNative([
            'name' => $variable->getName(),
            // TODO
        ])->toString();
    }

    public function serializeFieldSet(\Graphpinator\Normalizer\Field\FieldSet $fieldSet) : string
    {
        $temp = [];

        foreach ($fieldSet as $field) {
            $temp[] = $this->serializeField($field);
        }

        return \Infinityloop\Utils\Json::fromNative($temp)->toString();
    }

    public function serializeField(\Graphpinator\Normalizer\Field\Field $field) : string
    {
        return \Infinityloop\Utils\Json::fromNative([
            'fieldName' => $field->getField()->getName(),
            'alias' => $field->getAlias(),
            'argumentValueSet' => $this->serializeArgumentValueSet($field->getArguments()),
            'directiveSet' => $this->serializeDirectiveSet($field->getDirectives()),
            'fieldSet' => $field->getFields() === null
                ? null
                : $this->serializeFieldSet($field->getFields()),
            'typeCond' => $field->getTypeCondition()?->getName(),
        ])->toString();
    }

    public function serializeArgumentValueSet(\Graphpinator\Value\ArgumentValueSet $argumentValueSet) : string
    {
        $temp = [];

        foreach ($argumentValueSet as $argumentValue) {
            $temp[] = $this->serializeargumentValue($argumentValue);
        }

        return \Infinityloop\Utils\Json::fromNative($temp)->toString();
    }

    public function serializeArgumentValue(\Graphpinator\Value\ArgumentValue $argumentValue) : string
    {
        return \Infinityloop\Utils\Json::fromNative([
            'argument' => $argumentValue->getArgument()->getName(),
            'value' => $this->serializeInputedValue($argumentValue->getValue()),
        ])->toString();
    }

    public function serializeInputedValue(\Graphpinator\Value\InputedValue $inputedValue) : string
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

        return \Infinityloop\Utils\Json::fromNative($data)->toString();
    }
}
