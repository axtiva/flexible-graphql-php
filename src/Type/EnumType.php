<?php

namespace Axtiva\FlexibleGraphql\Type;

use GraphQL\Type\Definition\EnumType as BaseEnumType;

class EnumType extends BaseEnumType
{
    public function serialize(mixed $value): string
    {
        if ($value instanceof EnumInterface) {
            return (string) $value;
        }

        if (is_object($value) && isset($value->key) && is_string($value->key)) {
            return $value->key;
        }

        if ($value === null || is_scalar($value)) {
            return (string) $value;
        }

        return '';
    }
}
