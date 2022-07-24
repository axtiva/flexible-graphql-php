<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Utils;

use ReflectionClass;

class ObjectHelper
{
    public static function getClassShortName($object): string
    {
        return (new ReflectionClass($object))->getShortName();
    }

    public static function isClassImplements($object, $interface): bool
    {
        return (new ReflectionClass($object))->implementsInterface($interface);
    }
}