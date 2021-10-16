<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\Resolver\Psr\Container;

use Axtiva\FlexibleGraphql\Generator\Config\UnionResolveTypeGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\ResolverProvider\ResolverProviderInterface;
use GraphQL\Type\Definition\UnionType;
use Axtiva\FlexibleGraphql\Generator\Exception\NotDefinedResolver;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\UnionTypeResolverGeneratorInterface;

class UnionTypeGenerator implements UnionTypeResolverGeneratorInterface
{
    private ResolverProviderInterface $resolverProvider;
    private UnionResolveTypeGeneratorConfigInterface $unionConfig;

    public function __construct(
        UnionResolveTypeGeneratorConfigInterface $unionConfig,
        ResolverProviderInterface $resolverProvider
    ) {
        $this->resolverProvider = $resolverProvider;
        $this->unionConfig = $unionConfig;
    }

    public function generate(UnionType $type): string
    {
        if (file_exists($this->unionConfig->getModelClassFileName($type))) {
            return $this->resolverProvider->generate(
                $this->unionConfig->getModelFullClassName($type)
            );
        }

        throw new NotDefinedResolver($type->name);
    }
}