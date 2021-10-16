<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Resolver\Foundation;

use GraphQL\Language\AST\Node;
use Axtiva\FlexibleGraphql\Resolver\CustomScalarResolverInterface;

class DefaultScalarResolver implements CustomScalarResolverInterface
{
    public function serialize($value)
    {
        return $value;
    }

    public function parseValue($value)
    {
        return $value;
    }

    public function parseLiteral(Node $value, ?array $variables = null)
    {
        return $value->value;
    }
}