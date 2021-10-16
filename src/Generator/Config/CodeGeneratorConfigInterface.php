<?php

namespace Axtiva\FlexibleGraphql\Generator\Config;

interface CodeGeneratorConfigInterface
{
    public function getCodeNamespace(): ?string;
    public function getCodeDirPath(): string;
}