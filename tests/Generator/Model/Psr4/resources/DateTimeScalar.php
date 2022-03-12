<?php

declare (strict_types=1);
namespace Axtiva\FlexibleGraphql\Example\GraphQL\Scalar;

use Axtiva\FlexibleGraphql\Resolver\TypedCustomScalarResolverInterface;
use DateTimeImmutable;
use GraphQL\Language\AST\Node;

/**
 * This code is @generated by axtiva/flexible-graphql-php
 * This is resolver for scalar DateTime
 */
final class DateTimeScalar implements TypedCustomScalarResolverInterface
{
    public static function getTypeName(): ?string
    {
        return DateTimeImmutable::class;
    }

    function serialize($value)
    {
        return $value->format(DateTimeImmutable::ISO8601);
    }

    function parseValue($value)
    {
        return $value ? new DateTimeImmutable($value) : null;
    }

    function parseLiteral(Node $value, ?array $variables = null)
    {
        return $value->value ? new DateTimeImmutable((string) $value->value) : null;
    }
}