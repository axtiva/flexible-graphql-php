<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\Resolver\Psr\Container;

use Axtiva\FlexibleGraphql\Generator\Config\ScalarResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\ResolverProvider\ScalarResolverProviderInterface;
use GraphQL\Type\Definition\CustomScalarType;
use Axtiva\FlexibleGraphql\Generator\Exception\UnsupportedType;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\ScalarResolverGeneratorInterface;

class ScalarGenerator implements ScalarResolverGeneratorInterface
{
    private ScalarResolverProviderInterface $resolverProvider;
    private ScalarResolverGeneratorConfigInterface $config;

    public function __construct(
        ScalarResolverGeneratorConfigInterface $config,
        ScalarResolverProviderInterface $resolverProvider
    ) {
        $this->resolverProvider = $resolverProvider;
        $this->config = $config;
    }

    public function hasResolver(CustomScalarType $type): bool
    {
        return class_exists($this->config->getModelFullClassName($type));
    }

    public function generate(CustomScalarType $type): string
    {
        if ($this->hasResolver($type)) {
            return $this->resolverProvider->generate(
                $this->config,
                $type
            );
        }

        throw new UnsupportedType(get_class($type));
    }
}