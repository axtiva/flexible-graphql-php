<?php

namespace Axtiva\FlexibleGraphql\Generator\Model;

use Axtiva\FlexibleGraphql\Generator\Config\DirectiveResolverGeneratorConfigInterface;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Schema;

interface DirectiveResolverGeneratorInterface
{
    public function getConfig(): DirectiveResolverGeneratorConfigInterface;
    public function isSupportedType(Directive $directive): bool;
    public function generate(Directive $directive, Schema $schema): string;
}