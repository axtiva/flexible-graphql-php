<?php

namespace Axtiva\FlexibleGraphql\Generator\Model;

use Axtiva\FlexibleGraphql\Generator\Config\ArgsFieldResolverGeneratorConfigInterface;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;

interface ArgsFieldResolverModelGeneratorInterface
{
    public function getConfig(): ArgsFieldResolverGeneratorConfigInterface;
    public function isSupportedType(Type $type, FieldDefinition $field): bool;
    public function generate(Type $type, FieldDefinition $field, Schema $schema): string;
}