<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4;

use Axtiva\FlexibleGraphql\Generator\Config\CodeGeneratorConfigInterface;

class CodeGeneratorConfig implements CodeGeneratorConfigInterface
{
    private const VERSION_SUPPORTS = [self::V7_4, self::V8_0, self::V8_1];

    private ?string $codeNamespace;
    private string $codeDirPath;
    private string $phpVersion;

    public function __construct(string $dir, string $phpVersion, ?string $namespace = null)
    {
        if (! in_array($phpVersion, self::VERSION_SUPPORTS)) {
            $supported = implode(',', self::VERSION_SUPPORTS);
            throw new \InvalidArgumentException(
                'Not supported php version template ' . $phpVersion . ' use only ' . $supported
            );
        }
        $this->codeNamespace = $namespace ? trim($namespace, '\\') : null;
        $this->codeDirPath = rtrim($dir, '/');
        $this->phpVersion = $phpVersion;
    }

    public function getCodeNamespace(): ?string
    {
        return $this->codeNamespace;
    }

    public function getCodeDirPath(): string
    {
        return $this->codeDirPath;
    }

    public function getPHPVersion(): string
    {
        return $this->phpVersion;
    }
}