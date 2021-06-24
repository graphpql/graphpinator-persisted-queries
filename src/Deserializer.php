<?php

declare(strict_types = 1);

namespace Graphpinator\PersistedQueries;

final class Deserializer
{
    use \Nette\SmartObject;

    private \SplStack $typeStack;
    private \Graphpinator\Typesystem\Argument\ArgumentSet $currentArguments;
    private \Graphpinator\Typesystem\Argument\ArgumentSet $currentVariableSet;

    public function __construct(
        private \Graphpinator\Typesystem\Schema $schema,
    )
    {
        $this->typeStack = new \SplStack();
    }

    public function deserializeNormalizedRequest(string $data) : \Graphpinator\Normalizer\NormalizedRequest
    {
        $operationSet = (object) \Infinityloop\Utils\Json::fromString($data)->toNative();

        return new \Graphpinator\Normalizer\NormalizedRequest($this->deserializeOperationSet($operationSet));
    }

    public function deserializeOperationSet(\stdClass $operationSet) : \Graphpinator\Normalizer\Operation\OperationSet
    {
        $temp = [];

        foreach ($operationSet as $operation) {
            $temp[] = $this->deserializeOperation($operation);
        }

        return new \Graphpinator\Normalizer\Operation\OperationSet($temp);
    }

    public function deserializeOperation(\stdClass $operation) : \Graphpinator\Normalizer\Operation\Operation
    {
        $rootType = match ($operation->type) {
            \Graphpinator\Tokenizer\OperationType::QUERY => $this->schema->getQuery(),
            \Graphpinator\Tokenizer\OperationType::MUTATION => $this->schema->getMutation(),
            \Graphpinator\Tokenizer\OperationType::SUBSCRIPTION => $this->schema->getSubscription(),
        };
        $this->typeStack->push($rootType);

        $return = new \Graphpinator\Normalizer\Operation\Operation(
            $operation->type,
            $operation->name,
            $rootType,
            $this->deserializeFieldSet((object) $operation->fieldSet),
            $this->deserializeVariableSet((object) $operation->variableSet),
            $this->deserializeDirectiveSet((object) $operation->directiveSet),
        );

        $this->typeStack->pop();

        return $return;
    }

    public function deserializeFieldSet(\stdClass $fieldSet) : \Graphpinator\Normalizer\Field\FieldSet
    {
        $temp = [];

        foreach ($fieldSet as $field) {
            $temp[] = $this->deserializeField($field);
        }

        return new \Graphpinator\Normalizer\Field\FieldSet($temp);
    }

    public function deserializeField(\stdClass $field) : \Graphpinator\Normalizer\Field\Field
    {
        $fieldDef = $this->getField($field->fieldName);

        $this->typeStack->push($fieldDef->getType()->getNamedType());
        $this->currentArguments = $fieldDef->getArguments();

        $return = new \Graphpinator\Normalizer\Field\Field(
            $fieldDef,
            $field->alias,
            $this->deserializeArgumentValueSet((object) $field->argumentValueSet),
            $this->deserializeDirectiveSet((object) $field->directiveSet),
            $field->fieldSet === null
                ? null
                : $this->deserializeFieldSet((object) $field->fieldSet),
            $this->getTypeConditionable($field->typeCond),
        );

        $this->typeStack->pop();

        return $return;
    }

    private function deserializeDirectiveSet(\stdClass $directiveSet) : \Graphpinator\Normalizer\Directive\DirectiveSet
    {
        $temp = [];

        foreach ($directiveSet as $directive) {
            $temp[] = $this->deserializeDirective($directive);
        }

        return new \Graphpinator\Normalizer\Directive\DirectiveSet($temp);
    }

    private function deserializeDirective(\stdClass $directive) : \Graphpinator\Normalizer\Directive\Directive
    {
        $directiveDef = $this->schema->getContainer()->getDirective($directive->directive);
        $this->currentArguments = $directiveDef->getArguments();

        return new \Graphpinator\Normalizer\Directive\Directive(
            $directiveDef,
            $this->deserializeArgumentValueSet($directive->arguments),
        );
    }

    private function deserializeArgumentValueSet(\stdClass $argumentValueSet) : \Graphpinator\Value\ArgumentValueSet
    {
        $temp = [];

        foreach ($argumentValueSet as $argumentValue) {
            $temp[] = $this->deserializeArgumentValue($argumentValue);
        }

        return new \Graphpinator\Value\ArgumentValueSet($temp);
    }

    private function deserializeArgumentValue(\stdClass $argumentValue) : \Graphpinator\Value\ArgumentValue
    {
        $argument = $this->currentArguments->offsetGet($argumentValue->argument);
        $this->typeStack->push($argument->getType());

        $return = new \Graphpinator\Value\ArgumentValue(
            $argument,
            $this->deserializeInputedValue($argumentValue->value),
            true,
        );

        $this->typeStack->pop();

        return $return;
    }

    private function deserializeVariableSet(\stdClass $variableSet) : \Graphpinator\Normalizer\Variable\VariableSet
    {
        $temp = [];

        foreach ($variableSet as $variable) {
            $temp[] = $this->deserializeVariable($variable);
        }

        return new \Graphpinator\Normalizer\Variable\VariableSet($temp);
    }

    private function deserializeVariable(\stdClass $variable) : \Graphpinator\Normalizer\Variable\Variable
    {
        return new \Graphpinator\Normalizer\Variable\Variable(
            $variable->name,
            $this->deserializeType($variable->type),
            $this->deserializeInputedValue($variable->defaultValue),
        );
    }

    private function getField(string $fieldName) : \Graphpinator\Typesystem\Field\Field
    {
        $type = $this->typeStack->top();
        \assert($type instanceof \Graphpinator\Typesystem\Contract\Type);

        return $type->accept(new \Graphpinator\Normalizer\GetFieldVisitor($fieldName));
    }

    private function getTypeConditionable(?string $typeCond) : ?\Graphpinator\Typesystem\Contract\TypeConditionable
    {
        if ($typeCond === null) {
            return null;
        }

        $type = $this->typeStack->top();
        \assert($type instanceof \Graphpinator\Field\Field);

        return $type->getType()[$typeCond];
    }

    private function deserializeType(\stdClass $type) : \Graphpinator\Typesystem\Contract\Type
    {
        return match($type->type) {
            'named' => $this->schema->getContainer()->getType($type->name),
            'list' => new \Graphpinator\Type\ListType($this->deserializeType($type->inner)),
            'notnull' => new \Graphpinator\Type\NotNullType($this->deserializeType($type->inner)),
        };
    }

    private function deserializeInputedValue(\stdClass $inputedValue) : \Graphpinator\Value\InputedValue
    {
        switch ($inputedValue->valueType) {
            case \Graphpinator\Value\NullInputedValue::class:
                return new \Graphpinator\Value\NullInputedValue($this->deserializeType($inputedValue->type));
            case \Graphpinator\Value\ScalarValue::class:
                $scalarValue = new \Graphpinator\Value\ScalarValue(
                    $this->deserializeType($inputedValue->type),
                    $inputedValue->value,
                    false,
                );

                if (\isset($inputedValue->resolverValue)) {
                    $scalarValue->setResolverValue(\unserialize($inputedValue->resolverValue));
                }

                return $scalarValue;
            case \Graphpinator\Value\EnumValue::class:
                return new \Graphpinator\Value\EnumValue(
                    $this->deserializeType($inputedValue->type),
                    $inputedValue->value,
                    false,
                );
            case \Graphpinator\Value\VariableValue::class:
                return new \Graphpinator\Value\VariableValue(
                    $this->deserializeType($inputedValue->type),
                    $this->currentVariableSet->offsetGet($inputedValue->variableName),
                );
            case \Graphpinator\Value\ListInputedValue::class:
                $inner = [];

                foreach ($inputedValue->inner as $item) {
                    $inner[] = $this->deserializeInputedValue($item);
                }

                return new \Graphpinator\Value\ListInputedValue(
                    $this->deserializeType($inputedValue->type),
                    $inner,
                );
            case \Graphpinator\Value\InputValue::class:
                $inner = [];

                foreach ($inputedValue->inner as $item) {
                    $inner[] = $this->deserializeArgumentValue($item);
                }

                return new \Graphpinator\Value\InputValue(
                    $this->deserializeType($inputedValue->type),
                    (object) $inner,
                );
        }
    }
}
