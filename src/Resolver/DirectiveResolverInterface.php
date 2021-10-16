<?php

namespace Axtiva\FlexibleGraphql\Resolver;

use GraphQL\Type\Definition\ResolveInfo;

interface DirectiveResolverInterface
{
    /**
     * @param callable|ResolverInterface $next
     * @param array|null $directiveArgs contain list of arguments from current field directive
     * @param mixed $rootValue value from previous level resolver
     * @param array|null $args list of field arguments
     * @param mixed $context global context
     * @param ResolveInfo $info information about current field and ast node with full schema declaration
     */
    public function __invoke(callable $next, $directiveArgs, $rootValue, $args, $context, ResolveInfo $info);
}