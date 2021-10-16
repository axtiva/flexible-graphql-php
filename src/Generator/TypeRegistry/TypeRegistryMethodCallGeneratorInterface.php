<?php

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry;

use GraphQL\Type\Definition\Type;

interface TypeRegistryMethodCallGeneratorInterface
{
    public function getMethodCall(Type $type): string;
}