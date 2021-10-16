<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\Resolver\Psr\Container;

use Axtiva\FlexibleGraphql\Generator\Config\ScalarResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\ResolverProvider\ResolverProviderInterface;
use GraphQL\Type\Definition\CustomScalarType;
use Axtiva\FlexibleGraphql\Generator\Exception\UnsupportedType;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\ScalarResolverGeneratorInterface;

class ScalarGenerator implements ScalarResolverGeneratorInterface
{
    private ResolverProviderInterface $resolverProvider;
    private ScalarResolverGeneratorConfigInterface $config;

    public function __construct(
        ScalarResolverGeneratorConfigInterface $config,
        ResolverProviderInterface $resolverProvider
    ) {
        $this->resolverProvider = $resolverProvider;
        $this->config = $config;
    }

    public function hasResolver(CustomScalarType $type): bool
    {
        return file_exists($this->config->getModelClassFileName($type));
    }

    public function generate(CustomScalarType $type): string
    {
        if ($this->hasResolver($type)) {
            return $this->resolverProvider->generate(
                $this->config->getModelFullClassName($type)
            );
        }

        throw new UnsupportedType(get_class($type));
    }
}