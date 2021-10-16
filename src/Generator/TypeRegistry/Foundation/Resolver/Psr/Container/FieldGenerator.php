<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\Resolver\Psr\Container;

use Axtiva\FlexibleGraphql\Generator\Config\FieldResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\ResolverProvider\ResolverProviderInterface;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\Type;
use Axtiva\FlexibleGraphql\Generator\Exception\UnsupportedType;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\FieldResolverGeneratorInterface;

class FieldGenerator implements FieldResolverGeneratorInterface
{
    private ResolverProviderInterface $resolverProvider;
    private FieldResolverGeneratorConfigInterface $fieldConfig;

    public function __construct(
        FieldResolverGeneratorConfigInterface $fieldConfig,
        ResolverProviderInterface $resolverProvider
    ) {
        $this->resolverProvider = $resolverProvider;
        $this->fieldConfig = $fieldConfig;
    }

    public function hasResolver(Type $type, FieldDefinition $field): bool
    {
        return file_exists($this->fieldConfig->getFieldResolverClassFileName($type, $field));
    }

    public function generate(Type $type, FieldDefinition $field): string
    {
        if ($this->hasResolver($type, $field)) {
            $namespace = $this->fieldConfig->getFieldResolverNamespace($type, $field)
                ?  $this->fieldConfig->getFieldResolverNamespace($type, $field) . '\\'
                : '';
            return $this->resolverProvider->generate(
                $namespace . $this->fieldConfig->getFieldResolverClassName($type, $field)
            );
        }

        throw new UnsupportedType(sprintf('%s.%s', $type->name, $field->name));
    }
}