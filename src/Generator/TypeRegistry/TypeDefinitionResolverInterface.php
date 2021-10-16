<?php

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry;

use GraphQL\Type\Definition\Type;

interface TypeDefinitionResolverInterface
{
    public function getDefinition(Type $type): string;
}