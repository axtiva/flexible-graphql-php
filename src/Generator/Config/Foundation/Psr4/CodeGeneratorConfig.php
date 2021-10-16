<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4;

use Axtiva\FlexibleGraphql\Generator\Config\CodeGeneratorConfigInterface;

class CodeGeneratorConfig implements  CodeGeneratorConfigInterface
{
    private ?string $codeNamespace;
    private string $codeDirPath;

    public function __construct(string $dir, ?string $namespace = null)
    {
        $this->codeNamespace = $namespace ? trim($namespace, '\\') : null;
        $this->codeDirPath = rtrim($dir, '/');
    }

    public function getCodeNamespace(): ?string
    {
        return $this->codeNamespace;
    }

    public function getCodeDirPath(): string
    {
        return $this->codeDirPath;
    }
}