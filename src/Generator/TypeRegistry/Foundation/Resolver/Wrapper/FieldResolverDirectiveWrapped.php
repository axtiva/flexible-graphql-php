<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\Resolver\Wrapper;

use GraphQL\Language\AST\ArgumentNode;
use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\Type;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\DirectiveResolverGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\FieldResolverGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\Serializer\VariableSerializerInterface;

class FieldResolverDirectiveWrapped implements FieldResolverGeneratorInterface
{
    private FieldResolverGeneratorInterface $fieldResolverGenerator;
    private DirectiveResolverGeneratorInterface $directiveResolverGenerator;
    private VariableSerializerInterface $serializer;
    private FieldResolverGeneratorInterface $defaultFieldResolverGenerator;

    public function __construct(
        VariableSerializerInterface $serializer,
        FieldResolverGeneratorInterface $fieldResolverGenerator,
        FieldResolverGeneratorInterface $defaultFieldResolverGenerator,
        DirectiveResolverGeneratorInterface $directiveResolverGenerator
    ) {
        $this->fieldResolverGenerator = $fieldResolverGenerator;
        $this->directiveResolverGenerator = $directiveResolverGenerator;
        $this->serializer = $serializer;
        $this->defaultFieldResolverGenerator = $defaultFieldResolverGenerator;
    }

    public function hasResolver(Type $type, FieldDefinition $field): bool
    {
        $hasResolver = $this->fieldResolverGenerator->hasResolver($type, $field);
        if ($hasResolver) {
             return true;
        }
        foreach ($field->astNode->directives ?? [] as $directive) {
            if ($this->directiveResolverGenerator->hasResolver($directive)) {
                return true;
            }
        }

        return false;
    }

    public function generate(Type $type, FieldDefinition $field): string
    {
        $resolver = null;
        if ($this->fieldResolverGenerator->hasResolver($type, $field)) {
            $resolver = $this->fieldResolverGenerator->generate($type, $field);
        }
        /** @var DirectiveNode $directive */
        foreach ($field->astNode->directives ?? [] as $directive) {
            if ($this->directiveResolverGenerator->hasResolver($directive)) {
                if ($resolver === null) {
                    $resolver = $this->defaultFieldResolverGenerator->generate($type, $field);
                }
                $directiveResolver = $this->directiveResolverGenerator->generate($directive);
                $directiveArguments = [];
                /** @var ArgumentNode $argument */
                foreach ($directive->arguments as $argument) {
                    $directiveArguments[$argument->name->value] = $argument->value->value;
                }
                $resolver = "function(\$rootValue, \$args, \$context, \$info) {
                        return {$directiveResolver}(
                        {$resolver}, 
                        {$this->serializer->serialize($directiveArguments)},
                        \$rootValue, \$args, \$context, \$info
                        );
                    }";
            }
        }

        return $resolver;
    }
}