<?php

namespace Axtiva\FlexibleGraphql\Generator\ResolverProvider;

use Axtiva\FlexibleGraphql\Generator\Config\UnionResolveTypeGeneratorConfigInterface;
use GraphQL\Type\Definition\Type;

interface UnionResolverProviderInterface
{
    public function generate(UnionResolveTypeGeneratorConfigInterface $config, Type $type): string;
}