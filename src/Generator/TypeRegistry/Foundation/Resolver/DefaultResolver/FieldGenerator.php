<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\Resolver\DefaultResolver;

use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\Type;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\FieldResolverGeneratorInterface;

class FieldGenerator implements FieldResolverGeneratorInterface
{
    public function hasResolver(Type $type, FieldDefinition $field): bool
    {
        return true;
    }

    public function generate(Type $type, FieldDefinition $field): string
    {
        return '\Axtiva\FlexibleGraphql\Resolver\Foundation\DefaultResolver::getInstance()';
    }
}