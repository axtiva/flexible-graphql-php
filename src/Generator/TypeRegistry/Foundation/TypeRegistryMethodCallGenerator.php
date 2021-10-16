<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation;

use GraphQL\Type\Definition\Type;
use Axtiva\FlexibleGraphql\Generator\Serializer\VariableSerializerInterface;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\TypeRegistryMethodCallGeneratorInterface;

class TypeRegistryMethodCallGenerator implements TypeRegistryMethodCallGeneratorInterface
{
    private VariableSerializerInterface $serializer;

    public function __construct(VariableSerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function getMethodCall(Type $type): string
    {
        return "\$this->getType({$this->serializer->serialize($type->name)})";
    }
}