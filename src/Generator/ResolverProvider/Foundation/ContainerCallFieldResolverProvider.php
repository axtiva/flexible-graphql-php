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
        return sprintf(<<<'PHP'
(function ($rootValue, $args, $context, $info) {
    $resolver = $this->getService('%s');
    if (!\is_callable($resolver)) {
        throw new \RuntimeException('Resolver service is not callable: %s');
    }

    return $resolver($rootValue, $args, $context, $info);
})
PHP, $config->getFieldResolverFullClassName($type, $field), $config->getFieldResolverFullClassName($type, $field));
    }
}
