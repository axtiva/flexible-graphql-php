<?php

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry;

use GraphQL\Type\Definition\CustomScalarType;

interface ScalarResolverGeneratorInterface
{
    public function hasResolver(CustomScalarType $type): bool;
    public function generate(CustomScalarType $type): string;
}