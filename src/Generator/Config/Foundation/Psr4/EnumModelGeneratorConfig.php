<?php

namespace Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4;

use Axtiva\FlexibleGraphql\Generator\Config\CodeGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\EnumObjectGeneratorConfigInterface;
use GraphQL\Type\Definition\Type;

class EnumModelGeneratorConfig implements EnumObjectGeneratorConfigInterface
{
    private CodeGeneratorConfigInterface $config;

    public function __construct(CodeGeneratorConfigInterface $config)
    {
        $this->config = $config;
    }

    public function getModelNamespace(Type $type): ?string
    {
        return $this->config->getCodeNamespace() . '\\Model';
    }

    public function getModelClassName(Type $type): string
    {
        return $type->toString() . 'Enum';
    }

    public function getModelFullClassName(Type $type): string
    {
        return $this->getModelNamespace($type)
            ? $this->getModelNamespace($type) . '\\' . $this->getModelClassName($type)
            : $this->getModelClassName($type);
    }

    public function getModelClassFileName(Type $type): string
    {
        return $this->getModelDirPath($type) . '/' . $this->getModelClassName($type) . '.php';
    }

    public function getModelDirPath(Type $type): string
    {
        return $this->config->getCodeDirPath()
            . DIRECTORY_SEPARATOR
            . 'Model';
    }
}