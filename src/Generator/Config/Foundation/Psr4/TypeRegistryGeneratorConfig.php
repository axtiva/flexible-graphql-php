<?php

namespace Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4;

use Axtiva\FlexibleGraphql\Generator\Config\CodeGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\TypeRegistryGeneratorConfigInterface;

class TypeRegistryGeneratorConfig implements TypeRegistryGeneratorConfigInterface
{
    private CodeGeneratorConfigInterface $config;

    public function __construct(CodeGeneratorConfigInterface $config)
    {
        $this->config = $config;
    }

    public function getTypeRegistryNamespace(): ?string
    {
        return $this->config->getCodeNamespace();
    }

    public function getTypeRegistryClassName(): string
    {
        return 'TypeRegistry';
    }

    public function getTypeRegistryFullClassName(): string
    {
        return $this->getTypeRegistryNamespace()
            ? $this->getTypeRegistryNamespace() . '\\' . $this->getTypeRegistryClassName()
            : $this->getTypeRegistryClassName();
    }

    public function getTypeRegistryDirPath(): string
    {
        return $this->config->getCodeDirPath();
    }

    public function getTypeRegistryClassFileName(): string
    {
        return $this->getTypeRegistryDirPath() . '/' . $this->getTypeRegistryClassName() . '.php';
    }
}