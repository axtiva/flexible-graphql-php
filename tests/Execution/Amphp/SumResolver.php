<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Tests\Execution\Amphp;

use Axtiva\FlexibleGraphql\Resolver\ResolverInterface;
use ArrayAccess;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * A simple resolver for Mutation.sum that returns 1 + 1 = 2
 */
final class SumResolver implements ResolverInterface
{
    public function __invoke(mixed $rootValue, array|ArrayAccess|null $args, mixed $context, ResolveInfo $info): mixed
    {
        return 1 + 1;
    }
}
