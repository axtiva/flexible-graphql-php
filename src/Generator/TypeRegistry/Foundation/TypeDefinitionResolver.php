<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation;

use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\WrappingType;
use Axtiva\FlexibleGraphql\Generator\Exception\UnsupportedType;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\ScalarTypeGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\TypeDefinitionResolverInterface;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\TypeGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\TypeRegistryMethodCallGeneratorInterface;

class TypeDefinitionResolver implements TypeDefinitionResolverInterface
{
    private TypeGeneratorInterface $typeGenerator;
    private TypeRegistryMethodCallGeneratorInterface $methodCallGenerator;

    public function __construct(
        ScalarTypeGeneratorInterface             $typeGenerator,
        TypeRegistryMethodCallGeneratorInterface $methodCallGenerator
    ) {
        $this->typeGenerator = $typeGenerator;
        $this->methodCallGenerator = $methodCallGenerator;
    }

    public function getDefinition(Type $type): string
    {
        if ($this->typeGenerator->isSupportedType($type)) {
            return $this->typeGenerator->generate($type);
        } elseif ($type instanceof WrappingType) {
            switch (get_class($type)) {
                case NonNull::class:
                    return sprintf('Type::nonNull(function() { return %s; })', $this->getDefinition($type->getWrappedType()));
                case ListOfType::class:
                    return sprintf('new ListOfType(function() { return %s; })', $this->getDefinition($type->getWrappedType()));
            }

            throw new UnsupportedType(get_class($type));
        }

        return $this->methodCallGenerator->getMethodCall($type);
    }
}