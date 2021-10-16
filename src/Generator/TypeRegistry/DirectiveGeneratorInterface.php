<?php

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry;

use GraphQL\Type\Definition\Directive;

interface DirectiveGeneratorInterface
{
    public function generate(Directive $directive): string;
}