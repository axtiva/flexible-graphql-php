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
            throw new UnsupportedType($type->name);
        }

        return "
            public function {$this->nameGenerator->getMethodName($type)}()
            {
                return {$this->typeGenerator->generate($type)};
            }
        ";
    }
}