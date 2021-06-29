<?php

declare(strict_types = 1);

namespace Graphpinator\PersistedQueries\Bench;

class BenchQuery extends \Graphpinator\Typesystem\Type
{
    protected const NAME = 'Query';

    public function __construct(
        private \Graphpinator\Typesystem\Type $type,
    )
    {
        parent::__construct();
    }

    public function validateNonNullValue(mixed $rawValue) : bool
    {
        return true;
    }

    protected function getFieldDefinition() : \Graphpinator\Typesystem\Field\ResolvableFieldSet
    {
        return new \Graphpinator\Typesystem\Field\ResolvableFieldSet([
            \Graphpinator\Typesystem\Field\ResolvableField::create(
                'field',
                $this->type->notNull(),
                static function () : int {
                    return 1;
                },
            ),
        ]);
    }
}
