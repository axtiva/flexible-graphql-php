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

    public function serialize(mixed $value): mixed
    {
        return $value;
    }

    public function parseValue(mixed $value): mixed
    {
        return $value;
    }

    /**
     * @param array<string, mixed>|null $variables
     */
    public function parseLiteral(Node $value, ?array $variables = null): mixed
    {
        return get_object_vars($value)['value'] ?? null;
    }
}
