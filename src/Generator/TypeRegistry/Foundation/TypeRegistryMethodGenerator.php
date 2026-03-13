<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation;

use GraphQL\Type\Definition\Type;
use Axtiva\FlexibleGraphql\Generator\Exception\UnsupportedType;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\TypeGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\TypeRegistryMethodGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\TypeRegistryMethodNameGeneratorInterface;

class TypeRegistryMethodGenerator implements TypeRegistryMethodGeneratorInterface
{
    private TypeGeneratorInterface $typeGenerator;
    private TypeRegistryMethodNameGeneratorInterface $nameGenerator;

    public function __construct(
        TypeGeneratorInterface $typeGenerator,
        TypeRegistryMethodNameGeneratorInterface $nameGenerator
    ) {
        $this->typeGenerator = $typeGenerator;
        $this->nameGenerator = $nameGenerator;
    }

    public function getMethod(Type $type): string
    {
        if (false === $this->typeGenerator->isSupportedType($type)) {
            throw new UnsupportedType($type->toString());
        }

        $returnType = 'Type';
        if ($type instanceof \GraphQL\Type\Definition\InputObjectType) {
            $returnType = 'InputObjectType';
        } elseif ($type instanceof \GraphQL\Type\Definition\InterfaceType) {
            $returnType = 'InterfaceType';
        } elseif ($type instanceof \GraphQL\Type\Definition\ObjectType) {
            $returnType = 'ObjectType';
        } elseif ($type instanceof \GraphQL\Type\Definition\UnionType) {
            $returnType = 'UnionType';
        } elseif ($type instanceof \GraphQL\Type\Definition\CustomScalarType) {
            $returnType = 'CustomScalarType';
        } elseif ($type instanceof \GraphQL\Type\Definition\EnumType) {
            $returnType = 'EnumType';
        }

        return "
            public function {$this->nameGenerator->getMethodName($type)}(): {$returnType}
            {
                static \${$this->nameGenerator->getMethodName($type)} = null;
                if (\${$this->nameGenerator->getMethodName($type)} === null) {
                    \${$this->nameGenerator->getMethodName($type)} = {$this->typeGenerator->generate($type)};
                }

                return \${$this->nameGenerator->getMethodName($type)};
            }
        ";
    }
}
