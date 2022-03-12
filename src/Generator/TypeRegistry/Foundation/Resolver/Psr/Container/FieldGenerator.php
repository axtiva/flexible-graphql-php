<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\Resolver\Psr\Container;

use Axtiva\FlexibleGraphql\Generator\Config\FieldResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\ResolverProvider\FieldResolverProviderInterface;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\Type;
use Axtiva\FlexibleGraphql\Generator\Exception\UnsupportedType;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\FieldResolverGeneratorInterface;

class FieldGenerator implements FieldResolverGeneratorInterface
{
    private FieldResolverProviderInterface $resolverProvider;
    private FieldResolverGeneratorConfigInterface $fieldConfig;

    public function __construct(
        FieldResolverGeneratorConfigInterface $fieldConfig,
        FieldResolverProviderInterface $resolverProvider
    ) {
        $this->resolverProvider = $resolverProvider;
        $this->fieldConfig = $fieldConfig;
    }

    public function hasResolver(Type $type, FieldDefinition $field): bool
    {
        return \class_exists($this->fieldConfig->getFieldResolverFullClassName($type, $field));
    }

    public function generate(Type $type, FieldDefinition $field): string
    {
        if ($this->hasResolver($type, $field)) {
            return $this->resolverProvider->generate(
                $this->fieldConfig, $type, $field
            );
        }

        throw new UnsupportedType(sprintf('%s.%s', $type->name, $field->name));
    }
}