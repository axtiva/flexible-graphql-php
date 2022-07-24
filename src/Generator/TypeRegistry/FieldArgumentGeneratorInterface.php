<?php

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry;

use GraphQL\Type\Definition\Argument;

interface FieldArgumentGeneratorInterface
{
    public function generate(Argument $argument): string;
}