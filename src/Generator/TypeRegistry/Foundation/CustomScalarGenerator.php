<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation;

use GraphQL\Type\Definition\CustomScalarType;
use GraphQL\Type\Definition\Type;
use Axtiva\FlexibleGraphql\Generator\Exception\UnsupportedType;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\ScalarResolverGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\Serializer\VariableSerializerInterface;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\ScalarTypeGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\TypeGeneratorInterface;

class CustomScalarGenerator implements ScalarTypeGeneratorInterface
{
    private VariableSerializerInterface $serializer;
    private ScalarResolverGeneratorInterface $resolverGenerator;

    public function __construct(
        VariableSerializerInterface $serializer,
        ScalarResolverGeneratorInterface $resolverGenerator
    ) {
        $this->serializer = $serializer;
        $this->resolverGenerator = $resolverGenerator;
    }

    public function isSupportedType(Type $type): bool
    {
        return $type instanceof CustomScalarType;
    }

    /**
     * @param Type|CustomScalarType $type
     * @return string
     */
    public function generate(Type $type): string
    {
        if (false === $this->isSupportedType($type)) {
            throw new UnsupportedType(sprintf('Unsupported type %s for %s', $type->name, __CLASS__));
        }

        $resolvers = '';
        if ($this->resolverGenerator->hasResolver($type)) {
            $customResolver = $this->resolverGenerator->generate($type);
            $resolvers = <<<PHP
            'serialize' => function(\$value) {return ({$customResolver})->serialize(\$value);},
            'parseValue' => function(\$value) {return ({$customResolver})->parseValue(\$value);},
            'parseLiteral' => function(\$value, \$variables) {return ({$customResolver})->parseLiteral(\$value, \$variables);},
PHP;
        }

        return "new CustomScalarType([
            'name' => {$this->serializer->serialize($type->name)},
            'description' => {$this->serializer->serialize($type->description)},
{$resolvers}
        ])";
    }
}