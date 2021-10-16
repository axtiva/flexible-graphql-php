<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation;

use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\Type;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\FieldArgumentGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\FieldDefinitionGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\FieldResolverGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\Serializer\VariableSerializerInterface;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\TypeDefinitionResolverInterface;

class FieldDefinitionGenerator implements FieldDefinitionGeneratorInterface
{
    private VariableSerializerInterface $serializer;
    private FieldResolverGeneratorInterface $resolverGenerator;
    private FieldArgumentGeneratorInterface $fieldArgumentGenerator;
    private TypeDefinitionResolverInterface $typeDefinitionResolver;

    public function __construct(
        VariableSerializerInterface     $serializer,
        FieldResolverGeneratorInterface $fieldResolverGenerator,
        TypeDefinitionResolverInterface $typeDefinitionResolver,
        FieldArgumentGeneratorInterface $fieldArgumentGenerator
    ) {
        $this->serializer = $serializer;
        $this->resolverGenerator = $fieldResolverGenerator;
        $this->fieldArgumentGenerator = $fieldArgumentGenerator;
        $this->typeDefinitionResolver = $typeDefinitionResolver;
    }

    public function generate(Type $type, FieldDefinition $field): string
    {
        $args = [];
        foreach ($field->args as $arg) {
            $args[] = "{$this->serializer->serialize($arg->name)} => {$this->fieldArgumentGenerator->generate($arg)}" ;
        }
        $args = implode(',', $args);
        $resolve = '// No resolver. Default used';
        if ($this->resolverGenerator->hasResolver($type, $field)) {
            $resolve = "'resolve' => {$this->resolverGenerator->generate($type, $field)},";
        }
        return "FieldDefinition::create([
            'name' => {$this->serializer->serialize($field->name)},
            'description' => {$this->serializer->serialize($field->description)},
            'deprecationReason' => {$this->serializer->serialize($field->deprecationReason)},
            {$resolve}
            'type' => function() { return {$this->typeDefinitionResolver->getDefinition($field->getType())}; },
            'args' => [{$args}],
        ])";
    }
}