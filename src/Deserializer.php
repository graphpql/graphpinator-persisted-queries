<?php

declare(strict_types = 1);

namespace Graphpinator\PersistedQueries;

use Graphpinator\Tokenizer\OperationType;
use Graphpinator\Type\Type;

final class Deserializer
{
    use \Nette\SmartObject;

    private \SplStack $stack;

    public function __construct(
        private \Graphpinator\Typesystem\Schema $schema,
    )
    {

    }

    public function deserializeNormalizedRequest(string $data) : \Graphpinator\Normalizer\NormalizedRequest
    {
        $operationSet = \Infinityloop\Utils\Json::fromString($data)->toNative();

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
        $rootType = $this->getRootObject($operation->rootObject);
        $this->stack->push($rootType);

        $return = new \Graphpinator\Normalizer\Operation\Operation(
            $operation->type,
            $operation->name,
            $rootType,
            $this->deserializeFieldSet($operation->fieldSet),
            $this->deserializeVariableSet($operation->variableSet),
            $this->deserializeDirectiveSet($operation->directiveSet),
        );

        $this->stack->pop();

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
        return new \Graphpinator\Normalizer\Field\Field(
            $this->getField($field->fieldName),
            $field->alias,
            $this->deserializeArgumentValueSet($field->argumentValueSet),
            $this->deserializeDirectiveSet($field->directiveSet),
            $this->deserializeFieldSet($field->children),
            $this->getTypeConditionable($field->typeCond),
        );
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
        return new \Graphpinator\Normalizer\Directive\Directive(
            $this->getDirective($directive->directive),
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
        return new \Graphpinator\Value\ArgumentValue(
            $this->getArgument($argumentValue->argument),
            $this->deserializeInputedValue($argumentValue->value),
            true,
        );
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
        //TODO: Tady se bude asi vytahovat ze schématu?
    }

    private function getField(string $fieldName) : \Graphpinator\Field\Field
    {
        $type = $this->stack->top();
        \assert($type instanceof Type);

        return $type->getFields()[$fieldName];
    }

    private function getTypeConditionable(?string $typeCond) : ?\Graphpinator\Type\Contract\TypeConditionable
    {
        if ($typeCond === null) {
            return null;
        }

        //TODO: Najít typeConditionable ve schema?
    }

    private function getDirective(string $directiveName) : \Graphpinator\Directive\Contract\ExecutableDefinition
    {
        //TODO: Najít ExecutableDefinition ve schema?
    }

    private function getArgument(string $argumentName) : \Graphpinator\Argument\Argument
    {
        //TODO: Najít Argument\Argument ve schema?
    }

    private function getRootObject(string $rootObjectName) : \Graphpinator\Type\Type
    {
        //TODO: Najít RootObject ve schema?
        return match ($rootObjectName) {
            OperationType::QUERY => $this->schema->getQuery(),
            OperationType::MUTATION => $this->schema->getMutation(),
            OperationType::SUBSCRIPTION => $this->schema->getSubscription(),
        };
    }

    private function deserializeInputedValue(\stdClass $inputedValue) : \Graphpinator\Value\InputedValue
    {
        //TODO: Zavolat s Peldou a zjistit jak se tohle deserializuje
    }
}