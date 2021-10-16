<?php

namespace Axtiva\FlexibleGraphql\Type;

use GraphQL\Type\Definition\EnumType as BaseEnumType;

class EnumType extends BaseEnumType
{
    public function serialize($value): string
    {
        if ($value instanceof EnumInterface) {
            return (string) $value;
        }

        return $value->key;
    }
}