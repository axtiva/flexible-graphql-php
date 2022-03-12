<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Resolver\Foundation;

use GraphQL\Language\AST\Node;
use Axtiva\FlexibleGraphql\Resolver\CustomScalarResolverInterface;

class DefaultScalarResolver implements CustomScalarResolverInterface
{
    private static ?DefaultScalarResolver $instance = null;

    public static function getInstance(): CustomScalarResolverInterface
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

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