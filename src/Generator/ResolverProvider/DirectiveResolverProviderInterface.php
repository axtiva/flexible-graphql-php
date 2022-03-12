<?php

namespace Axtiva\FlexibleGraphql\Generator\ResolverProvider;

use Axtiva\FlexibleGraphql\Generator\Config\DirectiveResolverGeneratorConfigInterface;
use GraphQL\Type\Definition\Directive;

interface DirectiveResolverProviderInterface
{
    public function generate(DirectiveResolverGeneratorConfigInterface $config, Directive $directive): string;
}