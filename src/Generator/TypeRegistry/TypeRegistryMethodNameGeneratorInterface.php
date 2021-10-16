<?php

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry;

use GraphQL\Type\Definition\Type;

interface TypeRegistryMethodNameGeneratorInterface
{
    public function getMethodName(Type $type): string;
}