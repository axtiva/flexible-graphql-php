<?php

namespace Axtiva\FlexibleGraphql\Generator\Model;

use Axtiva\FlexibleGraphql\Generator\Config\FieldResolverGeneratorConfigInterface;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use PhpParser\Node;

interface FieldResolverGeneratorInterface
{
    public function getConfig(): FieldResolverGeneratorConfigInterface;
    public function isSupportedType(Type $type, FieldDefinition $field): bool;
    public function generate(Type $type, FieldDefinition $field, Schema $schema): string;
}