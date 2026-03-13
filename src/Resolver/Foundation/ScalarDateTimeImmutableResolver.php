<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Resolver\Foundation;

use DateTimeImmutable;
use GraphQL\Language\AST\Node;
use Axtiva\FlexibleGraphql\Resolver\CustomScalarResolverInterface;

class ScalarDateTimeImmutableResolver implements CustomScalarResolverInterface
{
    public function serialize(mixed $value): mixed
    {
        if (!($value instanceof DateTimeImmutable)) {
            return null;
        }

        return $value->format(DateTimeImmutable::ISO8601);
    }

    public function parseValue(mixed $value): mixed
    {
        if (!is_string($value) || $value === '') {
            return null;
        }

        return new DateTimeImmutable($value);
    }

    /**
     * @param array<string, mixed>|null $variables
     */
    public function parseLiteral(Node $value, ?array $variables = null): mixed
    {
        $literal = get_object_vars($value)['value'] ?? null;
        if (!is_string($literal) || $literal === '') {
            return null;
        }

        return new DateTimeImmutable($literal);
    }
}
