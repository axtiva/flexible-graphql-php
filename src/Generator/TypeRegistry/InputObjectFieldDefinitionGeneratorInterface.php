<?php

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry;

use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\Type;

interface InputObjectFieldDefinitionGeneratorInterface
{
    public function generate(Type $type, InputObjectField $field): string;
}