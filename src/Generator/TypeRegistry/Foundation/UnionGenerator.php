<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\UnionType;
use Axtiva\FlexibleGraphql\Generator\Exception\UnsupportedType;
use Axtiva\FlexibleGraphql\Generator\Serializer\VariableSerializerInterface;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\TypeGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\TypeRegistryMethodCallGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\UnionTypeResolverGeneratorInterface;

class UnionGenerator implements TypeGeneratorInterface
{
    private VariableSerializerInterface $serializer;
    private TypeRegistryMethodCallGeneratorInterface $methodGenerator;
    private UnionTypeResolverGeneratorInterface $resolverGenerator;

    public function __construct(
        VariableSerializerInterface $serializer,
        TypeRegistryMethodCallGeneratorInterface $methodGenerator,
        UnionTypeResolverGeneratorInterface $resolverGenerator
    ) {
        $this->serializer = $serializer;
        $this->methodGenerator = $methodGenerator;
        $this->resolverGenerator = $resolverGenerator;
    }

    public function isSupportedType(Type $type): bool
    {
        return $type instanceof UnionType;
    }

    /**
     * @param Type|UnionType $type
     * @return string
     */
    public function generate(Type $type): string
    {
        if (false === $this->isSupportedType($type)) {
            throw new UnsupportedType(sprintf('Unsupported type %s for %s', $type->name, __CLASS__));
        }

        $values = [];
        foreach ($type->getTypes() as $value) {
            $values[] = $this->methodGenerator->getMethodCall($value);
        }
        $values = implode(',', $values);
        return "new UnionType([
            'name' => {$this->serializer->serialize($type->name)},
            'description' => {$this->serializer->serialize($type->description)},
            'types' => function() { return [{$values}];},
            'resolveType' => {$this->resolverGenerator->generate($type)},
        ])";
    }
}