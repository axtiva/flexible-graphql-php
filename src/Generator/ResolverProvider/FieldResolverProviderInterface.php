<?php

namespace Axtiva\FlexibleGraphql\Generator\ResolverProvider;

use Axtiva\FlexibleGraphql\Generator\Config\FieldResolverGeneratorConfigInterface;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\Type;

interface FieldResolverProviderInterface
{
    public function generate(FieldResolverGeneratorConfigInterface $config, Type $type, FieldDefinition $field): string;
}