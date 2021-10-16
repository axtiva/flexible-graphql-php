<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Resolver\Foundation;

use GraphQL\Type\Definition\ResolveInfo;
use Axtiva\FlexibleGraphql\Resolver\DirectiveResolverInterface;

class UppercaseDirectiveResolver implements DirectiveResolverInterface
{
    public function __invoke(callable $next, $directiveArgs, $rootValue, $args, $context, ResolveInfo $info)
    {
        return mb_strtoupper($next($rootValue, $args, $context, $info));
    }
}