<?php

namespace Axtiva\FlexibleGraphql\Generator\Serializer;

interface VariableSerializerInterface
{
    public function serialize($value): string;
}