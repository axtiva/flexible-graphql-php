<?php

namespace Axtiva\FlexibleGraphql\Resolver;

use GraphQL\Type\Definition\ResolveInfo;

interface UnionResolveTypeInterface
{
    public function __invoke(mixed $model, mixed $context, ResolveInfo $info): mixed;
}
