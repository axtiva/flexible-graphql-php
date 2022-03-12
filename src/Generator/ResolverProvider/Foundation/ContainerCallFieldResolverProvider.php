<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\ResolverProvider\Foundation;

use Axtiva\FlexibleGraphql\Generator\Config\FieldResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\ResolverProvider\FieldResolverProviderInterface;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\Type;

class ContainerCallFieldResolverProvider implements FieldResolverProviderInterface
{
    public function generate(FieldResolverGeneratorConfigInterface $config, Type $type, FieldDefinition $field): string
    {
        return sprintf('$this->container->get(\'%s\')', $config->getFieldResolverFullClassName($type, $field));
    }
}