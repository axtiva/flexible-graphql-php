<?php

namespace Axtiva\FlexibleGraphql\Generator\Code;

use Axtiva\FlexibleGraphql\Generator\Code\Foundation\GeneratedCode;
use GraphQL\Type\Definition\CustomScalarType;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;

interface CodeGeneratorInterface
{
    /**
     * @return GeneratedCode[] class name and file path to generated files
     */
    public function generateAllTypes(Schema $schema): iterable;

    /**
     * @return GeneratedCode[] class name and file path to generated files
     */
    public function generateType(Type $type, Schema $schema): iterable;

    /**
     * @return GeneratedCode class name and file path to generated file
     */
    public function generateFieldResolver(Type $type, FieldDefinition $field, Schema $schema): GeneratedCode;

    /**
     * @return GeneratedCode path to generated file
     */
    public function generateDirectiveResolver(Directive $directive, Schema $schema): GeneratedCode;

    /**
     * @return GeneratedCode path to generated file
     */
    public function generateScalarResolver(CustomScalarType $scalar, Schema $schema): GeneratedCode;
}