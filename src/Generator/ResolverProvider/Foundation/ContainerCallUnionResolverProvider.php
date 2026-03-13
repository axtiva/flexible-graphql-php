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
        return sprintf(<<<'PHP'
(function ($model, $context, $info) {
    $resolver = $this->getService('%s');
    if (!\is_callable($resolver)) {
        throw new \RuntimeException('Union resolver service is not callable: %s');
    }

    return $resolver($model, $context, $info);
})
PHP, $config->getModelFullClassName($type), $config->getModelFullClassName($type));
    }
}
