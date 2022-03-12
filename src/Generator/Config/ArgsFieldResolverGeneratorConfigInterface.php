<?php

namespace Axtiva\FlexibleGraphql\Generator\Config;

use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\Type;

interface ArgsFieldResolverGeneratorConfigInterface extends LanguageLevelConfigInterface
{
    public function getResolverNamespace(): ?string;
    public function getResolverDirPath(): string;
    public function getFieldArgsNamespace(Type $type, FieldDefinition $field): ?string;
    public function getFieldArgsClassName(Type $type, FieldDefinition $field): string;
    public function getFieldArgsFullClassName(Type $type, FieldDefinition $field): string;
    public function getFieldArgsClassFileName(Type $type, FieldDefinition $field): string;
    public function getFieldArgsDirPath(Type $type, FieldDefinition $field): string;
}