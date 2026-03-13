<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation;

use GraphQL\Type\Definition\Type;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\TypeRegistryMethodCallGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\TypeRegistryMethodNameGeneratorInterface;

class TypeRegistryMethodCallGenerator implements TypeRegistryMethodCallGeneratorInterface
{
    private TypeRegistryMethodNameGeneratorInterface $nameGenerator;

    public function __construct(TypeRegistryMethodNameGeneratorInterface $nameGenerator)
    {
        $this->nameGenerator = $nameGenerator;
    }

    public function getMethodCall(Type $type): string
    {
        return sprintf('$this->%s()', $this->nameGenerator->getMethodName($type));
    }
}
