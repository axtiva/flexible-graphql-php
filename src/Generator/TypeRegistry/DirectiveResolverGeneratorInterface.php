<?php

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry;

use GraphQL\Language\AST\DirectiveNode;

interface DirectiveResolverGeneratorInterface
{
    public function hasResolver(DirectiveNode $directive): bool;
    public function generate(DirectiveNode $directive): string;
}