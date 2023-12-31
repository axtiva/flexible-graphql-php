<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\Resolver\Wrapper;

use Axtiva\FlexibleGraphql\Generator\Config\ArgsDirectiveResolverGeneratorConfigInterface;
use GraphQL\Language\AST\ArgumentNode;
use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\ListValueNode;
use GraphQL\Language\AST\NullValueNode;
use GraphQL\Type\Definition\Directive;
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
    private ArgsDirectiveResolverGeneratorConfigInterface $argsDirectiveResolverGeneratorConfig;

    public function __construct(
        VariableSerializerInterface $serializer,
        FieldResolverGeneratorInterface $fieldResolverGenerator,
        FieldResolverGeneratorInterface $defaultFieldResolverGenerator,
        DirectiveResolverGeneratorInterface $directiveResolverGenerator,
        ArgsDirectiveResolverGeneratorConfigInterface $argsDirectiveResolverGeneratorConfig
    ) {
        $this->fieldResolverGenerator = $fieldResolverGenerator;
        $this->directiveResolverGenerator = $directiveResolverGenerator;
        $this->serializer = $serializer;
        $this->defaultFieldResolverGenerator = $defaultFieldResolverGenerator;
        $this->argsDirectiveResolverGeneratorConfig = $argsDirectiveResolverGeneratorConfig;
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
        $resolver = $this->defaultFieldResolverGenerator->generate($type, $field);
        if ($this->fieldResolverGenerator->hasResolver($type, $field)) {
            $resolver = $this->fieldResolverGenerator->generate($type, $field);
        }
        /** @var DirectiveNode $directive */
        foreach ($field->astNode->directives ?? [] as $directive) {
            if ($this->directiveResolverGenerator->hasResolver($directive)) {
                $directiveResolver = $this->directiveResolverGenerator->generate($directive);
                $directiveArguments = [];
                $recursive = function ($value) use (&$recursive) {
                    if ($value instanceof ListValueNode) {
                        return array_map($recursive, iterator_to_array($value->values->getIterator()));
                    }
                    return $value instanceof NullValueNode ? null : $value->value;
                };
                /** @var ArgumentNode $argument */
                foreach ($directive->arguments as $argument) {
                    $directiveArguments[$argument->name->value] = $recursive($argument->value);
                }

                $directiveArgs = $this->serializer->serialize($directiveArguments);
                $directiveDefinition = new Directive([
                    'name' => $directive->name->value,
                    'locations' => [],
                ]);
                if (class_exists($this->argsDirectiveResolverGeneratorConfig->getDirectiveArgsFullClassName($directiveDefinition))) {
                    $directiveArgs = sprintf(
                        'new \\%s(%s)',
                            ltrim($this->argsDirectiveResolverGeneratorConfig->getDirectiveArgsFullClassName($directiveDefinition), '\\'),
                            $directiveArgs
                        );
                }

                $resolver = "function(\$rootValue, \$args, \$context, \$info) {
                        return {$directiveResolver}(
                        {$resolver}, 
                        {$directiveArgs},
                        \$rootValue, \$args, \$context, \$info
                        );
                    }";
            }
        }

        return $resolver;
    }
}