<?php

namespace Axtiva\FlexibleGraphql\Resolver;

use GraphQL\Language\AST\Node;

interface CustomScalarResolverInterface
{
    public function serialize($value);
    public function parseValue($value);
    public function parseLiteral(Node $value, ?array $variables = null);
}