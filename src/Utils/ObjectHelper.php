<?php

namespace Axtiva\FlexibleGraphql\Utils;

use ReflectionClass;

class ObjectHelper
{
    public static function getClassShortName($object): string
    {
        return (new ReflectionClass($object))->getShortName();
    }

    public static function isClassImplements($object, $interface): string
    {
        return (new ReflectionClass($object))->implementsInterface($interface);
    }
}