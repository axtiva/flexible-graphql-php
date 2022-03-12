<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\ResolverProvider\Foundation;

use Axtiva\FlexibleGraphql\Generator\Config\UnionResolveTypeGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\ResolverProvider\UnionResolverProviderInterface;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\Type;

class ContainerCallUnionResolverProvider implements UnionResolverProviderInterface
{
    public function generate(UnionResolveTypeGeneratorConfigInterface $config, Type $type): string
    {
        return sprintf('$this->container->get(\'%s\')', $config->getModelFullClassName($type));
    }
}