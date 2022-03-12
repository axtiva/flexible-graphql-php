<?php

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\Resolver\DefaultResolver;

use Axtiva\FlexibleGraphql\Generator\Config\ScalarResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\ScalarResolverGeneratorInterface;
use GraphQL\Type\Definition\CustomScalarType;
use GraphQL\Type\Definition\Type;

class ScalarGenerator implements ScalarResolverGeneratorInterface
{
    public function hasResolver(CustomScalarType $type): bool
    {
        return true;
    }

    public function generate(CustomScalarType $type): string
    {
        return '\Axtiva\FlexibleGraphql\Resolver\Foundation\DefaultScalarResolver::getInstance()';
    }
}