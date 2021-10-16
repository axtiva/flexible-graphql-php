<?php

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry;

use GraphQL\Type\Definition\UnionType;

interface UnionTypeResolverGeneratorInterface
{
    public function generate(UnionType $type): string;
}