<?php

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry;

use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\Type;

interface FieldDefinitionGeneratorInterface
{
    public function generate(Type $type, FieldDefinition $field): string;
}