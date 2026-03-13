<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Resolver\Foundation;

use GraphQL\Type\Definition\ResolveInfo;
use Axtiva\FlexibleGraphql\Resolver\DirectiveResolverInterface;
use ArrayAccess;

class UppercaseDirectiveResolver implements DirectiveResolverInterface
{
    public function __invoke(callable $next, array|ArrayAccess|null $directiveArgs, mixed $rootValue, array|ArrayAccess|null $args, mixed $context, ResolveInfo $info): mixed
    {
        return mb_strtoupper($next($rootValue, $args, $context, $info));
    }
}
