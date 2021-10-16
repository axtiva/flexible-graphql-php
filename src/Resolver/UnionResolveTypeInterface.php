<?php

namespace Axtiva\FlexibleGraphql\Resolver;

use GraphQL\Type\Definition\ResolveInfo;

interface UnionResolveTypeInterface
{
    public function __invoke($model, $context, ResolveInfo $info);
}