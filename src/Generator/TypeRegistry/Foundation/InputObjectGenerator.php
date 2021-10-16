<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use Axtiva\FlexibleGraphql\Generator\Exception\UnsupportedType;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\InputObjectFieldDefinitionGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\Serializer\VariableSerializerInterface;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\TypeGeneratorInterface;

class InputObjectGenerator implements TypeGeneratorInterface
{
    private VariableSerializerInterface $serializer;
    private InputObjectFieldDefinitionGeneratorInterface $fieldDefinitionGenerator;

    public function __construct(
        VariableSerializerInterface $serializer,
        InputObjectFieldDefinitionGeneratorInterface $fieldDefinitionGenerator
    ) {
        $this->serializer = $serializer;
        $this->fieldDefinitionGenerator = $fieldDefinitionGenerator;
    }

    public function isSupportedType(Type $type): bool
    {
        return $type instanceof InputObjectType;
    }

    /**
     * @param InputObjectType $type
     * @return string
     */
    public function generate(Type $type): string
    {
        if (false === $this->isSupportedType($type)) {
            throw new UnsupportedType(sprintf('Unsupported type %s for %s', $type->name, __CLASS__));
        }

        $fields = [];
        foreach ($type->getFields() as $field) {
            $fields[] = "{$this->serializer->serialize($field->name)} => {$this->fieldDefinitionGenerator->generate($type, $field)}";
        }
        $fields = implode(',', $fields);

        $resolver = "fn() => [{$fields}]";

        return "new InputObjectType([
        'name' => {$this->serializer->serialize($type->name)},
        'description' =>  {$this->serializer->serialize($type->description)},
        'fields' => {$resolver},
        ])";
    }
}