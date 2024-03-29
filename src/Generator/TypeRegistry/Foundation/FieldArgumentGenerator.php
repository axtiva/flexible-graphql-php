<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation;

use GraphQL\Type\Definition\Argument;
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

    public function generate(Argument $argument): string
    {
        $description = '';
        if (isset($argument->description)) {
            $description = "\n            'description' => {$this->serializer->serialize($argument->description)},";
        }

        $defaultValue = '';
        if (isset($argument->defaultValue)) {
            $defaultValue = "\n            'defaultValue' => {$this->serializer->serialize($argument->defaultValue)},";
        }
        return "[
            'name' => {$this->serializer->serialize($argument->name)},
            'type' => function() { return {$this->typeDefinitionResolver->getDefinition($argument->getType())}; },{$description}{$defaultValue}
        ]";
    }

}