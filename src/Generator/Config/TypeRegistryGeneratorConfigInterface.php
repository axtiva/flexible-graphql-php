<?php

namespace Axtiva\FlexibleGraphql\Generator\Config;

interface TypeRegistryGeneratorConfigInterface extends LanguageLevelConfigInterface
{
    public function getTypeRegistryNamespace(): ?string;
    public function getTypeRegistryClassName(): string;
    public function getTypeRegistryFullClassName(): string;
    public function getTypeRegistryClassFileName(): string;
    public function getTypeRegistryDirPath(): string;
}