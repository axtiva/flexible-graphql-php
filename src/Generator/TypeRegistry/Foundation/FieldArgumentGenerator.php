<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation;

use GraphQL\Type\Definition\FieldArgument;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\FieldArgumentGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\Serializer\VariableSerializerInterface;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\TypeDefinitionResolverInterface;

class FieldArgumentGenerator implements FieldArgumentGeneratorInterface
{
    private VariableSerializerInterface $serializer;
    private TypeDefinitionResolverInterface $typeDefinitionResolver;

    public function __construct(
        VariableSerializerInterface $serializer,
        TypeDefinitionResolverInterface $typeDefinitionResolver
    ) {
        $this->serializer = $serializer;
        $this->typeDefinitionResolver = $typeDefinitionResolver;
    }

    public function generate(FieldArgument $type): string
    {
        return "[
            'name' => {$this->serializer->serialize($type->name)},
            'type' => function() { return {$this->typeDefinitionResolver->getDefinition($type->getType())}; },
            'defaultValue' => {$this->serializer->serialize($type->defaultValue)},
            'description' => {$this->serializer->serialize($type->description)},
        ]";
    }

}