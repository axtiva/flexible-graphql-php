<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\Resolver\Psr\Container;

use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\Type;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\FieldResolverGeneratorInterface;

class DefaultFieldGenerator implements FieldResolverGeneratorInterface
{
    private string $defaultResolver;

    public function __construct(
        string $defaultResolver
    ) {
        $this->defaultResolver = $defaultResolver;
    }

    public function hasResolver(Type $type, FieldDefinition $field): bool
    {
        return true;
    }

    public function generate(Type $type, FieldDefinition $field): string
    {
        return $this->defaultResolver;
    }
}