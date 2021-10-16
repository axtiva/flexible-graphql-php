<?php

namespace Axtiva\FlexibleGraphql\Generator\Model;

use Axtiva\FlexibleGraphql\Generator\Config\DirectiveResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\FieldResolverGeneratorConfigInterface;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use PhpParser\Node;

interface DirectiveResolverGeneratorInterface
{
    public function getConfig(): DirectiveResolverGeneratorConfigInterface;
    public function isSupportedType(Directive $directive): bool;
    public function generate(Directive $directive, Schema $schema): string;
}