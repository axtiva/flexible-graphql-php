<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4;

use Axtiva\FlexibleGraphql\Generator\Config\CodeGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\FieldResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\GetPHPVersionFromCodeGeneratorTrait;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\Type;

class FieldResolverGeneratorConfig implements FieldResolverGeneratorConfigInterface
{
    use GetPHPVersionFromCodeGeneratorTrait;

    protected CodeGeneratorConfigInterface $config;

    public function __construct(CodeGeneratorConfigInterface $config)
    {
        $this->config = $config;
    }

    public function getResolverNamespace(): ?string
    {
        return $this->config->getCodeNamespace() . '\\Resolver';
    }

    public function getResolverDirPath(): string
    {
        return $this->config->getCodeDirPath() . DIRECTORY_SEPARATOR . 'Resolver';
    }

    public function getFieldResolverNamespace(Type $type, FieldDefinition $field): ?string
    {
        return $this->getResolverNamespace() ? $this->getResolverNamespace() . "\\{$type->toString()}" : null;
    }

    public function getFieldResolverClassName(Type $type, FieldDefinition $field): string
    {
        return ucfirst($field->getName()) . 'Resolver';
    }

    public function getFieldResolverFullClassName(Type $type, FieldDefinition $field): string
    {
        return $this->getFieldResolverNamespace($type, $field)
            ? $this->getFieldResolverNamespace($type, $field) . '\\' . $this->getFieldResolverClassName($type, $field)
            : $this->getFieldResolverClassName($type, $field);
    }

    public function getFieldResolverDirPath(Type $type, FieldDefinition $field): string
    {
        return $this->getResolverDirPath() . \DIRECTORY_SEPARATOR . "{$type->toString()}";
    }

    public function getFieldResolverClassFileName(Type $type, FieldDefinition $field): string
    {
        return $this->getFieldResolverDirPath($type, $field)
            . \DIRECTORY_SEPARATOR
            . $this->getFieldResolverClassName($type, $field) . '.php';
    }
}