<?php

declare(strict_types = 1);

namespace Graphpinator\PersistedQueries;

final class Deserializer
{
    use \Nette\SmartObject;

    private \SplStack $typeStack;
    private \Graphpinator\Typesystem\Argument\ArgumentSet $currentArguments;
    private \Graphpinator\Normalizer\Variable\VariableSet $currentVariableSet;

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

    private function deserializeOperationSet(\stdClass $operationSet) : \Graphpinator\Normalizer\Operation\OperationSet
    {
        $temp = [];

        foreach ($operationSet as $operation) {
            \assert($this->typeStack->isEmpty());
            $temp[] = $this->deserializeOperation($operation);
        }

        return new \Graphpinator\Normalizer\Operation\OperationSet($temp);
    }

    private function deserializeOperation(\stdClass $operation) : \Graphpinator\Normalizer\Operation\Operation
    {
        $rootType = match ($operation->type) {
            \Graphpinator\Tokenizer\OperationType::QUERY => $this->schema->getQuery(),
            \Graphpinator\Tokenizer\OperationType::MUTATION => $this->schema->getMutation(),
            \Graphpinator\Tokenizer\OperationType::SUBSCRIPTION => $this->schema->getSubscription(),
        };
        $this->typeStack->push($rootType);
        $variables = $this->deserializeVariableSet((object) $operation->variableSet);

        $return = new \Graphpinator\Normalizer\Operation\Operation(
            $operation->type,
            $operation->name,
            $rootType,
            $this->deserializeSelectionSet((object) $operation->selectionSet),
            $variables,
            $this->deserializeDirectiveSet((object) $operation->directiveSet),
        );

        $this->typeStack->pop();

        return $return;
    }

    private function deserializeSelectionSet(\stdClass $selectionSet) : \Graphpinator\Normalizer\Selection\SelectionSet
    {
        $temp = [];

        foreach ($selectionSet as $selection) {
            $temp[] = match ($selection->selectionType) {
                \Graphpinator\Normalizer\Selection\Field::class => $this->deserializeField($selection),
                \Graphpinator\Normalizer\Selection\FragmentSpread::class => $this->deserializeFragmentSpread($selection),
                \Graphpinator\Normalizer\Selection\InlineFragment::class => $this->deserializeInlineFragment($selection),
            };
        }

        return new \Graphpinator\Normalizer\Selection\SelectionSet($temp);
    }

    private function deserializeField(\stdClass $field) : \Graphpinator\Normalizer\Selection\Field
    {
        $parentType = $this->typeStack->top();
        \assert($parentType instanceof \Graphpinator\Typesystem\Contract\Type);
        $fieldDef = $parentType->accept(new \Graphpinator\Normalizer\GetFieldVisitor($field->fieldName));

        $this->typeStack->push($fieldDef->getType()->getNamedType());
        $this->currentArguments = $fieldDef->getArguments();

        $return = new \Graphpinator\Normalizer\Selection\Field(
            $fieldDef,
            $field->alias,
            $this->deserializeArgumentValueSet((object) $field->argumentValueSet),
            $this->deserializeDirectiveSet((object) $field->directiveSet),
            $field->selectionSet === null
                ? null
                : $this->deserializeSelectionSet((object) $field->selectionSet),
        );

        $this->typeStack->pop();

        return $return;
    }

    private function deserializeFragmentSpread(\stdClass $fragmentSpread) : \Graphpinator\Normalizer\Selection\FragmentSpread
    {
        $typeCond = $this->deserializeType($fragmentSpread->typeCond);
        $this->typeStack->push($typeCond);

        $return = new \Graphpinator\Normalizer\Selection\FragmentSpread(
            $fragmentSpread->fragmentName,
            $this->deserializeSelectionSet((object) $fragmentSpread->selectionSet),
            $this->deserializeDirectiveSet((object) $fragmentSpread->directiveSet),
            $typeCond,
        );

        $this->typeStack->pop();

        return $return;
    }

    private function deserializeInlineFragment(\stdClass $inlineSpread) : \Graphpinator\Normalizer\Selection\InlineFragment
    {
        $typeCond = $this->deserializeType($inlineSpread->typeCond);
        $this->typeStack->push($typeCond
            ?? $this->typeStack->top());

        $return = new \Graphpinator\Normalizer\Selection\InlineFragment(
            $this->deserializeSelectionSet((object) $inlineSpread->selectionSet),
            $this->deserializeDirectiveSet((object) $inlineSpread->directiveSet),
            $typeCond,
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
            $this->deserializeArgumentValueSet((object) $directive->arguments),
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

        $set = new \Graphpinator\Normalizer\Variable\VariableSet($temp);
        $this->currentVariableSet = $set;

        return $set;
    }

    private function deserializeVariable(\stdClass $variable) : \Graphpinator\Normalizer\Variable\Variable
    {
        return new \Graphpinator\Normalizer\Variable\Variable(
            $variable->name,
            $this->deserializeType($variable->type),
            $variable->defaultValue === null
                ? null
                : $this->deserializeInputedValue($variable->defaultValue),
        );
    }

    private function deserializeType(\stdClass $type) : \Graphpinator\Typesystem\Contract\Type
    {
        return match ($type->type) {
            'named' => $this->schema->getContainer()->getType($type->name),
            'list' => new \Graphpinator\Typesystem\ListType($this->deserializeType($type->inner)),
            'notnull' => new \Graphpinator\Typesystem\NotNullType($this->deserializeType($type->inner)),
        };
    }

    private function deserializeScalarValue(\stdClass $inputedValue) : \Graphpinator\Value\ScalarValue
    {
        $scalarValue = new \Graphpinator\Value\ScalarValue(
            $this->deserializeType($inputedValue->type),
            $inputedValue->value,
            false,
        );

        if (isset($inputedValue->resolverValue)) {
            $scalarValue->setResolverValue(\unserialize($inputedValue->resolverValue));
        }

        return $scalarValue;
    }

    private function deserializeListInputedValue(\stdClass $inputedValue) : \Graphpinator\Value\ListInputedValue
    {
        $inner = [];

        foreach ($inputedValue->inner as $item) {
            $inner[] = $this->deserializeInputedValue($item);
        }

        return new \Graphpinator\Value\ListInputedValue(
            $this->deserializeType($inputedValue->type),
            $inner,
        );
    }

    private function deserializeInputValue(\stdClass $inputedValue) : \Graphpinator\Value\InputValue
    {
        $inner = [];
        $type = $this->deserializeType($inputedValue->type);
        \assert($type instanceof \Graphpinator\Typesystem\InputType);
        $currentArgumentsBackup = $this->currentArguments;
        $this->currentArguments = $type->getArguments();

        foreach ($inputedValue->inner as $key => $item) {
            $inner[$key] = $this->deserializeArgumentValue($item);
        }

        $this->currentArguments = $currentArgumentsBackup;

        return new \Graphpinator\Value\InputValue(
            $type,
            (object) $inner,
        );
    }

    private function deserializeInputedValue(\stdClass $inputedValue) : \Graphpinator\Value\InputedValue
    {
        return match ($inputedValue->valueType) {
            \Graphpinator\Value\NullInputedValue::class => new \Graphpinator\Value\NullInputedValue($this->deserializeType($inputedValue->type)),
            \Graphpinator\Value\ScalarValue::class => $this->deserializeScalarValue($inputedValue),
            \Graphpinator\Value\EnumValue::class => new \Graphpinator\Value\EnumValue(
                $this->deserializeType($inputedValue->type),
                $inputedValue->value,
                false,
            ),
            \Graphpinator\Value\VariableValue::class => new \Graphpinator\Value\VariableValue(
                $this->deserializeType($inputedValue->type),
                $this->currentVariableSet->offsetGet($inputedValue->variableName),
            ),
            \Graphpinator\Value\ListInputedValue::class => $this->deserializeListInputedValue($inputedValue),
            \Graphpinator\Value\InputValue::class => $this->deserializeInputValue($inputedValue),
        };
    }
}
