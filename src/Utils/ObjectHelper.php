<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Utils;

use ReflectionClass;

class ObjectHelper
{
    /**
     * @param string|object $object
     */
    public static function getClassShortName(string|object $object): string
    {
        if (
            is_string($object)
            && !class_exists($object)
            && !interface_exists($object)
            && !trait_exists($object)
        ) {
            return $object;
        }

        /** @var class-string|object $reflectionTarget */
        $reflectionTarget = $object;

        return (new ReflectionClass($reflectionTarget))->getShortName();
    }

    /**
     * @param string|object $object
     * @param string $interface
     */
    public static function isClassImplements(string|object $object, string $interface): bool
    {
        if (
            is_string($object)
            && !class_exists($object)
            && !interface_exists($object)
            && !trait_exists($object)
        ) {
            return false;
        }

        /** @var class-string|object $reflectionTarget */
        $reflectionTarget = $object;

        return (new ReflectionClass($reflectionTarget))->implementsInterface($interface);
    }
}
