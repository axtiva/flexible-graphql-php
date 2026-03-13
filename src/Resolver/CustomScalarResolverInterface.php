<?php

namespace Axtiva\FlexibleGraphql\Resolver;

use GraphQL\Language\AST\Node;

interface CustomScalarResolverInterface
{
    public function serialize(mixed $value): mixed;
    public function parseValue(mixed $value): mixed;

    /**
     * @param array<string, mixed>|null $variables
     */
    public function parseLiteral(Node $value, ?array $variables = null): mixed;
}
