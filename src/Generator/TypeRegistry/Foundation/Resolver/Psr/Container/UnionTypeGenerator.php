<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\Resolver\Psr\Container;

use Axtiva\FlexibleGraphql\Generator\Config\UnionResolveTypeGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\ResolverProvider\UnionResolverProviderInterface;
use GraphQL\Type\Definition\UnionType;
use Axtiva\FlexibleGraphql\Generator\Exception\NotDefinedResolver;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\UnionTypeResolverGeneratorInterface;

class UnionTypeGenerator implements UnionTypeResolverGeneratorInterface
{
    private UnionResolverProviderInterface $resolverProvider;
    private UnionResolveTypeGeneratorConfigInterface $unionConfig;

    public function __construct(
        UnionResolveTypeGeneratorConfigInterface $unionConfig,
        UnionResolverProviderInterface $resolverProvider
    ) {
        $this->resolverProvider = $resolverProvider;
        $this->unionConfig = $unionConfig;
    }

    public function generate(UnionType $type): string
    {
        if (class_exists($this->unionConfig->getModelFullClassName($type))) {
            return $this->resolverProvider->generate(
                $this->unionConfig,
                $type
            );
        }

        throw new NotDefinedResolver($type->name);
    }
}