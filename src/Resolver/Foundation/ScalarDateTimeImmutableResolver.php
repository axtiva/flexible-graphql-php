<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Resolver\Foundation;

use DateTimeImmutable;
use GraphQL\Language\AST\Node;
use Axtiva\FlexibleGraphql\Resolver\CustomScalarResolverInterface;

class ScalarDateTimeImmutableResolver implements CustomScalarResolverInterface
{
    /**
     * @param DateTimeImmutable $value
     * @return string
     */
    public function serialize($value)
    {
        return $value->format(DateTimeImmutable::ISO8601);
    }

    public function parseValue($value)
    {
        return $value ? new DateTimeImmutable($value) : null;
    }

    public function parseLiteral(Node $value, ?array $variables = null)
    {
        return $value->value ? new DateTimeImmutable((string) $value->value) : null;
    }
}