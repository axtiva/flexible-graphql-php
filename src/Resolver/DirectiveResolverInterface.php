<?php

namespace Axtiva\FlexibleGraphql\Resolver;

use ArrayAccess;
use GraphQL\Type\Definition\ResolveInfo;

interface DirectiveResolverInterface
{
    /**
     * @param callable|ResolverInterface $next
     * @param array|ArrayAccess|null $directiveArgs contain list of arguments from current field directive
     * @param mixed $rootValue value from previous level resolver
     * @param array|ArrayAccess|null $args list of field arguments
     * @param mixed $context global context
     * @param ResolveInfo $info information about current field and ast node with full schema declaration
     */
    public function __invoke(callable $next, $directiveArgs, $rootValue, $args, $context, ResolveInfo $info);
}