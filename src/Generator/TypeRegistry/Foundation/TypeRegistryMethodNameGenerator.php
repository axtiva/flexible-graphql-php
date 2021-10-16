<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation;

use GraphQL\Type\Definition\Type;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\TypeRegistryMethodNameGeneratorInterface;

class TypeRegistryMethodNameGenerator implements TypeRegistryMethodNameGeneratorInterface
{
    public function getMethodName(Type $type): string
    {
        return $type->name;
    }
}