<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation;

use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\Type;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\InputObjectFieldDefinitionGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\Serializer\VariableSerializerInterface;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\TypeDefinitionResolverInterface;

class InputObjectFieldDefinitionGenerator implements InputObjectFieldDefinitionGeneratorInterface
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

    public function generate(Type $type, InputObjectField $field): string
    {
        $description = '';
        if (isset($field->description)) {
            $description = "\n            'description' => {$this->serializer->serialize($field->description)},";
        }

        $defaultValue = '';
        if (isset($field->defaultValue)) {
            $defaultValue = "\n            'defaultValue' => {$this->serializer->serialize($field->defaultValue)},";
        }

        return "[
            'name' => {$this->serializer->serialize($field->name)},{$description}{$defaultValue}
            'type' => {$this->typeDefinitionResolver->getDefinition($field->getType())},
        ]";
    }
}