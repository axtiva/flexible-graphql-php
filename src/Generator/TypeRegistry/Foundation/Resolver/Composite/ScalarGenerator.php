<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\Resolver\Composite;

use GraphQL\Type\Definition\CustomScalarType;
use Axtiva\FlexibleGraphql\Generator\Exception\UnsupportedType;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\ScalarResolverGeneratorInterface;

class ScalarGenerator implements ScalarResolverGeneratorInterface
{
    /**
     * @var ScalarResolverGeneratorInterface[]
     */
    private array $resolvers = [];

    public function __construct(ScalarResolverGeneratorInterface ...$resolvers)
    {
        $this->resolvers = $resolvers;
    }

    public function hasResolver(CustomScalarType $type): bool
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->hasResolver($type)) {
                return true;
            }
        }

        return false;
    }

    public function generate(CustomScalarType $type): string
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->hasResolver($type)) {
                return $resolver->generate($type);
            }
        }

        throw new UnsupportedType(get_class($type));
    }
}