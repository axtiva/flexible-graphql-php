<?php

namespace Axtiva\FlexibleGraphql\Generator\Model;

use Axtiva\FlexibleGraphql\Generator\Config\ArgsDirectiveResolverGeneratorConfigInterface;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Schema;

interface ArgsDirectiveResolverModelGeneratorInterface
{
    public function getConfig(): ArgsDirectiveResolverGeneratorConfigInterface;
    public function isSupportedType(Directive $directive): bool;
    public function generate(Directive $directive, Schema $schema): string;
}