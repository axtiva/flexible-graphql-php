<?php

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry;

use GraphQL\Type\Definition\Type;

interface TypeRegistryMethodGeneratorInterface
{
    public function getMethod(Type $type): string;
}