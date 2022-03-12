<?php

namespace Axtiva\FlexibleGraphql\Resolver;

use GraphQL\Language\AST\Node;

interface TypedCustomScalarResolverInterface extends CustomScalarResolverInterface
{
    public static function getTypeName(): ?string;
    public function serialize($value);
    public function parseValue($value);
    public function parseLiteral(Node $value, ?array $variables = null);
}