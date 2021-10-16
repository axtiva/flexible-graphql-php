<?php

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry;

use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\Type;

interface FieldResolverGeneratorInterface
{
    public function hasResolver(Type $type, FieldDefinition $field): bool;
    public function generate(Type $type, FieldDefinition $field): string;
}