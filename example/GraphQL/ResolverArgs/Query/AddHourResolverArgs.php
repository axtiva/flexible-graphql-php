<?php

declare (strict_types=1);
namespace Axtiva\FlexibleGraphql\Example\GraphQL\ResolverArgs\Query;

use Axtiva\FlexibleGraphql\Type\InputType;
use DateTimeImmutable;

/**
 * This code is @generated by axtiva/flexible-graphql-php do not edit it
 * PHP representation of graphql field args of Query.addHour
 * @property DateTimeImmutable $date 
 */
final class AddHourResolverArgs extends InputType
{
    protected function decorate($name, $value)
    {
        if ($value === null) {
            return null;
        }

        return $value;
    }
}