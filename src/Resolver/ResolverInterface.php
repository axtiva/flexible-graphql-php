<?php

namespace Axtiva\FlexibleGraphql\Resolver;

use GraphQL\Type\Definition\ResolveInfo;

interface ResolverInterface
{
    public function __invoke($rootValue, $args, $context, ResolveInfo $info);
}