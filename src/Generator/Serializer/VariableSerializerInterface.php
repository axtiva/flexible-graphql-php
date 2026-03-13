<?php

namespace Axtiva\FlexibleGraphql\Generator\Serializer;

interface VariableSerializerInterface
{
    public function serialize(mixed $value): string;
}
