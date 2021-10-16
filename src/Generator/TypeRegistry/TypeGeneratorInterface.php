<?php

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry;

use GraphQL\Type\Definition\Type;

interface TypeGeneratorInterface
{
    public function isSupportedType(Type $type): bool;
    public function generate(Type $type): string;
}