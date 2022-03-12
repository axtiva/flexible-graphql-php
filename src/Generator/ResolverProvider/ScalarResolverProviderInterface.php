<?php

namespace Axtiva\FlexibleGraphql\Generator\ResolverProvider;

use Axtiva\FlexibleGraphql\Generator\Config\ScalarResolverGeneratorConfigInterface;
use GraphQL\Type\Definition\Type;

interface ScalarResolverProviderInterface
{
    public function generate(ScalarResolverGeneratorConfigInterface $config, Type $type): string;
}