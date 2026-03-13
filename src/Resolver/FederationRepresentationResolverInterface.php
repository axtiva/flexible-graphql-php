<?php

namespace Axtiva\FlexibleGraphql\Resolver;

use Axtiva\FlexibleGraphql\Representation;
use GraphQL\Type\Definition\ResolveInfo;

interface FederationRepresentationResolverInterface
{
    public function getTypeName(): string;
    public function __invoke(Representation $representation, mixed $context, ResolveInfo $info): mixed;
}
