<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4;

use Axtiva\FlexibleGraphql\Generator\Config\ArgsFieldResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\CodeGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\GetPHPVersionFromCodeGeneratorTrait;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\Type;

class ArgsFieldResolverGeneratorConfig implements ArgsFieldResolverGeneratorConfigInterface
{
    use GetPHPVersionFromCodeGeneratorTrait;

    protected CodeGeneratorConfigInterface $config;

    public function __construct(CodeGeneratorConfigInterface $config)
    {
        $this->config = $config;
    }

    public function getResolverNamespace(): ?string
    {
        return $this->config->getCodeNamespace() . '\\ResolverArgs';
    }

    public function getResolverDirPath(): string
    {
        return $this->config->getCodeDirPath() . \DIRECTORY_SEPARATOR . 'ResolverArgs';
    }

    public function getFieldArgsNamespace(Type $type, FieldDefinition $field): ?string
    {
        return $this->getResolverNamespace() ? $this->getResolverNamespace() . "\\{$type->toString()}" : null;
    }

    public function getFieldArgsClassName(Type $type, FieldDefinition $field): string
    {
        return ucfirst($field->getName()) . 'ResolverArgs';
    }

    public function getFieldArgsFullClassName(Type $type, FieldDefinition $field): string
    {
        return $this->getFieldArgsNamespace($type, $field)
            ? $this->getFieldArgsNamespace($type, $field) . '\\' . $this->getFieldArgsClassName($type, $field)
            : $this->getFieldArgsClassName($type, $field);
    }

    public function getFieldArgsDirPath(Type $type, FieldDefinition $field): string
    {
        return $this->getResolverDirPath() . \DIRECTORY_SEPARATOR . $type->toString();
    }

    public function getFieldArgsClassFileName(Type $type, FieldDefinition $field): string
    {
        return $this->getFieldArgsDirPath($type, $field)
            . \DIRECTORY_SEPARATOR
            . $this->getFieldArgsClassName($type, $field) . '.php';
    }
}