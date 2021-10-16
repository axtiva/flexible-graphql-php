<?php

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry;

use GraphQL\Type\Definition\FieldArgument;

interface FieldArgumentGeneratorInterface
{
    public function generate(FieldArgument $directive): string;
}