<?php

namespace Axtiva\FlexibleGraphql\Resolver;

use ArrayAccess;
use GraphQL\Type\Definition\ResolveInfo;

interface DirectiveResolverInterface
{
    /**
     * @param callable|ResolverInterface $next
     * @param array<string, mixed>|ArrayAccess<string, mixed>|null $directiveArgs contain list of arguments from current field directive
     * @param mixed $rootValue value from previous level resolver
     * @param array<string, mixed>|ArrayAccess<string, mixed>|null $args list of field arguments
     * @param mixed $context global context
     * @param ResolveInfo $info information about current field and ast node with full schema declaration
     */
    public function __invoke(callable $next, array|ArrayAccess|null $directiveArgs, mixed $rootValue, array|ArrayAccess|null $args, mixed $context, ResolveInfo $info): mixed;
}
