<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\Resolver\Composite;

use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\Type;
use Axtiva\FlexibleGraphql\Generator\Exception\UnsupportedType;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\FieldResolverGeneratorInterface;

class FieldGenerator implements FieldResolverGeneratorInterface
{
    /**
     * @var FieldResolverGeneratorInterface[]
     */
    private array $resolvers = [];

    public function __construct(FieldResolverGeneratorInterface ...$resolvers)
    {
        $this->resolvers = $resolvers;
    }

    public function hasResolver(Type $type, FieldDefinition $field): bool
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->hasResolver($type, $field)) {
                return true;
            }
        }

        return false;
    }

    public function generate(Type $type, FieldDefinition $field): string
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->hasResolver($type, $field)) {
                return $resolver->generate($type, $field);
            }
        }

        throw new UnsupportedType(get_class($type), get_class($field));
    }
}