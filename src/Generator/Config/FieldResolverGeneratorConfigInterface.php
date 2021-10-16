<?php

namespace Axtiva\FlexibleGraphql\Generator\Config;

use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\Type;

interface FieldResolverGeneratorConfigInterface
{
    public function getResolverNamespace(): ?string;
    public function getResolverDirPath(): string;
    public function getFieldResolverNamespace(Type $type, FieldDefinition $field): ?string;
    public function getFieldResolverClassName(Type $type, FieldDefinition $field): string;
    public function getFieldResolverFullClassName(Type $type, FieldDefinition $field): string;
    public function getFieldResolverClassFileName(Type $type, FieldDefinition $field): string;
    public function getFieldResolverDirPath(Type $type, FieldDefinition $field): string;
}