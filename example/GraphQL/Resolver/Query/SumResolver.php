<?php

declare (strict_types=1);
namespace Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\Query;

use Axtiva\FlexibleGraphql\Resolver\ResolverInterface;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * This code is @generated by axtiva/flexible-graphql-php
 * This is resolver for Query.sum
 */
final class SumResolver implements ResolverInterface
{
    /**
     * @param $rootValue
     * @param $args
     * @param $context
     * @param ResolveInfo $info
     * @return ?int
     */
    public function __invoke($rootValue, $args, $context, ResolveInfo $info)
    {
        return 1 + 1;
    }
}