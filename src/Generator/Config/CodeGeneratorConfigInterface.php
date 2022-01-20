<?php

namespace Axtiva\FlexibleGraphql\Generator\Config;

interface CodeGeneratorConfigInterface extends LanguageLevelConfigInterface
{
    public function getCodeNamespace(): ?string;
    public function getCodeDirPath(): string;
}